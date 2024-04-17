<?php

namespace TinySolutions\cptwooint\Modal;

use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\CptProductDataStoreReadTrait;
use WC_Product_Data_Store_CPT;
/**
 * Data Store
 */
class CPTProductDataStore extends WC_Product_Data_Store_CPT {
	/**
	 * Method to read a product from the database.
	 *
	 * @param \WC_Product
	 */
	use CptProductDataStoreReadTrait;

	/**
	 * Search product data for a term and return ids.
	 *
	 * @param  string     $term Search term.
	 * @param  string     $type Type of product.
	 * @param  bool       $include_variations Include variations in search or not.
	 * @param  bool       $all_statuses Should we search all statuses or limit to published.
	 * @param  null|int   $limit Limit returned results. @since 3.5.0.
	 * @param  null|array $include Keep specific results. @since 3.6.0.
	 * @param  null|array $exclude Discard specific results. @since 3.6.0.
	 * @return array of ids
	 */
	public function search_products( $term, $type = '', $include_variations = false, $all_statuses = false, $limit = null, $include = null, $exclude = null ) {
		$post_type = get_post_type( sanitize_text_field( wp_unslash( $_REQUEST['exclude'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! Fns::is_supported( $post_type ) ) {
			return parent::search_products( $term, $type, $include_variations, $all_statuses, $limit, $include, $exclude );
		}

		global $wpdb;

		$post_types   = $include_variations ? [ $post_type, 'product', 'product_variation' ] : [ 'product', $post_type ];
		$join_query   = '';
		$type_where   = '';
		$status_where = '';
		$limit_query  = '';

		// When searching variations we should include the parent's meta table for use in searches.
		if ( $include_variations ) {
			$join_query = " LEFT JOIN {$wpdb->wc_product_meta_lookup} parent_wc_product_meta_lookup
			 ON posts.post_type = 'product_variation' AND parent_wc_product_meta_lookup.product_id = posts.post_parent ";
		}

		/**
		 * Hook woocommerce_search_products_post_statuses.
		 *
		 * @since 3.7.0
		 * @param array $post_statuses List of post statuses.
		 */
		$post_statuses = apply_filters(
			'woocommerce_search_products_post_statuses',
			current_user_can( 'edit_private_products' ) ? [ 'private', 'publish' ] : [ 'publish' ]
		);

		// See if search term contains OR keywords.
		if ( stristr( $term, ' or ' ) ) {
			$term_groups = preg_split( '/\s+or\s+/i', $term );
		} else {
			$term_groups = [ $term ];
		}

		$search_where   = '';
		$search_queries = [];

		foreach ( $term_groups as $term_group ) {
			// Parse search terms.
			if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $term_group, $matches ) ) {
				$search_terms = $this->get_valid_search_terms( $matches[0] );
				$count        = count( $search_terms );

				// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
				if ( 9 < $count || 0 === $count ) {
					$search_terms = [ $term_group ];
				}
			} else {
				$search_terms = [ $term_group ];
			}

			$term_group_query = '';
			$searchand        = '';

			foreach ( $search_terms as $search_term ) {
				$like = '%' . $wpdb->esc_like( $search_term ) . '%';

				// Variations should also search the parent's meta table for fallback fields.
				if ( $include_variations ) {
					$variation_query = $wpdb->prepare( " OR ( wc_product_meta_lookup.sku = '' AND parent_wc_product_meta_lookup.sku LIKE %s ) ", $like );
				} else {
					$variation_query = '';
				}

				$term_group_query .= $wpdb->prepare( " {$searchand} ( ( posts.post_title LIKE %s) OR ( posts.post_excerpt LIKE %s) OR ( posts.post_content LIKE %s ) OR ( wc_product_meta_lookup.sku LIKE %s ) $variation_query)", $like, $like, $like, $like ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$searchand         = ' AND ';
			}

			if ( $term_group_query ) {
				$search_queries[] = $term_group_query;
			}
		}

		if ( ! empty( $search_queries ) ) {
			$search_where = ' AND (' . implode( ') OR (', $search_queries ) . ') ';
		}

		if ( ! empty( $include ) && is_array( $include ) ) {
			$search_where .= ' AND posts.ID IN(' . implode( ',', array_map( 'absint', $include ) ) . ') ';
		}

		if ( ! empty( $exclude ) && is_array( $exclude ) ) {
			$search_where .= ' AND posts.ID NOT IN(' . implode( ',', array_map( 'absint', $exclude ) ) . ') ';
		}

		if ( 'virtual' === $type ) {
			$type_where = ' AND ( wc_product_meta_lookup.virtual = 1 ) ';
		} elseif ( 'downloadable' === $type ) {
			$type_where = ' AND ( wc_product_meta_lookup.downloadable = 1 ) ';
		}

		if ( ! $all_statuses ) {
			$status_where = " AND posts.post_status IN ('" . implode( "','", $post_statuses ) . "') ";
		}

		if ( $limit ) {
			$limit_query = $wpdb->prepare( ' LIMIT %d ', $limit );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$search_results = $wpdb->get_results(
		// phpcs:disable
			"SELECT DISTINCT posts.ID as product_id, posts.post_parent as parent_id FROM {$wpdb->posts} posts
			 LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON posts.ID = wc_product_meta_lookup.product_id
			 $join_query
			WHERE posts.post_type IN ('" . implode( "','", $post_types ) . "')
			$search_where
			$status_where
			$type_where
			ORDER BY posts.post_parent ASC, posts.post_title ASC
			$limit_query
			"
		// phpcs:enable
		);

		$product_ids = wp_parse_id_list( array_merge( wp_list_pluck( $search_results, 'product_id' ), wp_list_pluck( $search_results, 'parent_id' ) ) );

		if ( is_numeric( $term ) ) {
			$post_id     = absint( $term );
			$n_post_type = get_post_type( $post_id );

			if ( 'product_variation' === $n_post_type && $include_variations ) {
				$product_ids[] = $post_id;
			} elseif ( 'product' === $n_post_type || $post_type === $n_post_type ) {
				$product_ids[] = $post_id;
			}

			$product_ids[] = wp_get_post_parent_id( $post_id );
		}

		return wp_parse_id_list( $product_ids );
	}
}
