<?php
/**
 * Hestia functions and definitions
 *
 * @package Hestia
 * @since   Hestia 1.0
 */

define( 'HESTIA_VERSION', '3.1.8' );
define( 'HESTIA_VENDOR_VERSION', '1.0.2' );
define( 'HESTIA_PHP_INCLUDE', trailingslashit( get_template_directory() ) . 'inc/' );
define( 'HESTIA_CORE_DIR', HESTIA_PHP_INCLUDE . 'core/' );

if ( ! defined( 'HESTIA_DEBUG' ) ) {
	define( 'HESTIA_DEBUG', false );
}

// Load hooks
require_once( HESTIA_PHP_INCLUDE . 'hooks/hooks.php' );

// Load Helper Globally Scoped Functions
require_once( HESTIA_PHP_INCLUDE . 'helpers/sanitize-functions.php' );
require_once( HESTIA_PHP_INCLUDE . 'helpers/layout-functions.php' );

if ( class_exists( 'WooCommerce', false ) ) {
	require_once( HESTIA_PHP_INCLUDE . 'compatibility/woocommerce/functions.php' );
}

if ( function_exists( 'max_mega_menu_is_enabled' ) ) {
	require_once( HESTIA_PHP_INCLUDE . 'compatibility/max-mega-menu/functions.php' );
}

// Load starter content
require_once( HESTIA_PHP_INCLUDE . 'compatibility/class-hestia-starter-content.php' );


/**
 * Adds notice for PHP < 5.3.29 hosts.
 */
function hestia_no_support_5_3() {
	$message = __( 'Hey, we\'ve noticed that you\'re running an outdated version of PHP which is no longer supported. Make sure your site is fast and secure, by upgrading PHP to the latest version.', 'hestia' );

	printf( '<div class="error"><p>%1$s</p></div>', esc_html( $message ) );
}


if ( version_compare( PHP_VERSION, '5.3.29' ) < 0 ) {
	/**
	 * Add notice for PHP upgrade.
	 */
	add_filter( 'template_include', '__return_null', 99 );
	switch_theme( WP_DEFAULT_THEME );
	unset( $_GET['activated'] );
	add_action( 'admin_notices', 'hestia_no_support_5_3' );

	return;
}

/**
 * Begins execution of the theme core.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function hestia_run() {

	require_once HESTIA_CORE_DIR . 'class-hestia-autoloader.php';
	$autoloader = new Hestia_Autoloader();

	spl_autoload_register( array( $autoloader, 'loader' ) );

	new Hestia_Core();

	$vendor_file = trailingslashit( get_template_directory() ) . 'vendor/composer/autoload_files.php';
	if ( is_readable( $vendor_file ) ) {
		$files = require_once $vendor_file;
		foreach ( $files as $file ) {
			if ( is_readable( $file ) ) {
				include_once $file;
			}
		}
	}
	add_filter( 'themeisle_sdk_products', 'hestia_load_sdk' );

	if ( class_exists( 'Ti_White_Label', false ) ) {
		Ti_White_Label::instance( get_template_directory() . '/style.css' );
	}
}

/**
 * Loads products array.
 *
 * @param array $products All products.
 *
 * @return array Products array.
 */
function hestia_load_sdk( $products ) {
	$products[] = get_template_directory() . '/style.css';

	return $products;
}

require_once( HESTIA_CORE_DIR . 'class-hestia-autoloader.php' );

/**
 * The start of the app.
 *
 * @since   1.0.0
 */
hestia_run();

/**
 * Append theme name to the upgrade link
 * If the active theme is child theme of Hestia
 *
 * @param string $link - Current link.
 *
 * @return string $link - New upgrade link.
 * @package hestia
 * @since   1.1.75
 */
function hestia_upgrade_link( $link ) {

	$theme_name = wp_get_theme()->get_stylesheet();

	$hestia_child_themes = array(
		'orfeo',
		'fagri',
		'tiny-hestia',
		'christmas-hestia',
		'jinsy-magazine',
	);

	if ( $theme_name === 'hestia' ) {
		return $link;
	}

	if ( ! in_array( $theme_name, $hestia_child_themes, true ) ) {
		return $link;
	}

	$link = add_query_arg(
		array(
			'theme' => $theme_name,
		),
		$link
	);

	return $link;
}

add_filter( 'hestia_upgrade_link_from_child_theme_filter', 'hestia_upgrade_link' );

/**
 * Check if $no_seconds have passed since theme was activated.
 * Used to perform certain actions, like displaying upsells or add a new recommended action in About Hestia page.
 *
 * @param integer $no_seconds number of seconds.
 *
 * @return bool
 * @since  1.1.45
 * @access public
 */
function hestia_check_passed_time( $no_seconds ) {
	$activation_time = get_option( 'hestia_time_activated' );
	if ( ! empty( $activation_time ) ) {
		$current_time    = time();
		$time_difference = (int) $no_seconds;
		if ( $current_time >= $activation_time + $time_difference ) {
			return true;
		} else {
			return false;
		}
	}

	return true;
}

/**
 * Legacy code function.
 */
function hestia_setup_theme() {
	return;
}

/**
 * Minimize CSS.
 *
 * @param string $css Inline CSS.
 * @return string
 */
function hestia_minimize_css( $css ) {
	if ( empty( $css ) ) {
		return $css;
	}
	// Normalize whitespace.
	$css = preg_replace( '/\s+/', ' ', $css );
	// Remove spaces before and after comment.
	$css = preg_replace( '/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $css );
	// Remove comment blocks, everything between /* and */, unless.
	// preserved with /*! ... */ or /** ... */.
	$css = preg_replace( '~/\*(?![\!|\*])(.*?)\*/~', '', $css );
	// Remove ; before }.
	$css = preg_replace( '/;(?=\s*})/', '', $css );
	// Remove space after , : ; { } */ >.
	$css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );
	// Remove space before , ; { } ( ) >.
	$css = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $css );
	// Strips leading 0 on decimal values (converts 0.5px into .5px).
	$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
	// Strips units if value is 0 (converts 0px to 0).
	$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
	// Converts all zeros value into short-hand.
	$css = preg_replace( '/0 0 0 0/', '0', $css );
	// Shortern 6-character hex color codes to 3-character where possible.
	$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );
	return trim( $css );
}

// Регистрация нового типа записи "Фильмы"
function custom_post_type_movies() {
    $args = array(
        'public' => true,
        'label'  => 'Фильмы',
        'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
    );
    register_post_type( 'movies', $args );
}
add_action( 'init', 'custom_post_type_movies' );

// Добавление таксономии "Жанры" к типу записи "Фильмы"
function custom_taxonomy_genres() {
    $args = array(
        'hierarchical' => true,
        'label' => 'Жанры',
        'show_ui' => true, // Показывать в административной панели
        'show_admin_column' => true, // Показывать в колонке административной панели
        'query_var' => true,
        'rewrite' => array( 'slug' => 'genres' ),
    );
    register_taxonomy( 'genres', 'movies', $args );
}
add_action( 'init', 'custom_taxonomy_genres' );

// Добавление таксономии "Страны" к типу записи "Фильмы"
function custom_taxonomy_countries() {
    $args = array(
        'hierarchical' => true,
        'label' => 'Страны',
        'show_ui' => true, // Показывать в административной панели
        'show_admin_column' => true, // Показывать в колонке административной панели
        'query_var' => true,
        'rewrite' => array( 'slug' => 'countries' ),
    );
    register_taxonomy( 'countries', 'movies', $args );
}
add_action( 'init', 'custom_taxonomy_countries' );

// Добавление таксономии "Актеры" к типу записи "Фильмы"
function custom_taxonomy_actors() {
    $args = array(
        'hierarchical' => false,
        'label' => 'Актеры',
        'show_ui' => true, // Показывать в административной панели
        'show_admin_column' => true, // Показывать в колонке административной панели
        'query_var' => true,
        'rewrite' => array( 'slug' => 'actors' ),
    );
    register_taxonomy( 'actors', 'movies', $args );
}
add_action( 'init', 'custom_taxonomy_actors' );


//
function enqueue_movies_scripts() {
    // Подключаем jQuery
    wp_enqueue_script('jquery');

    // Подключаем наш скрипт functions.js
    wp_enqueue_script('movies-scripts', get_template_directory_uri() . '/functions.js', array('jquery'), null, true);

    // Локализация переменных для использования в скрипте
    wp_localize_script('movies-scripts', 'movies_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_movies_scripts');



// AJAX для фильтрации и сортировки фильмов
add_action('wp_ajax_filter_movies', 'filter_movies');
add_action('wp_ajax_nopriv_filter_movies', 'filter_movies');

function filter_movies() {
    $args = array(
        'post_type' => 'movies',
        'posts_per_page' => -1,
        'tax_query' => array(),
        'meta_query' => array(),
    );

    // Добавляем условия для таксономий (жанры, страны, актеры)
    if (isset($_GET['genre-filter']) && is_array($_GET['genre-filter'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'genres',
            'field' => 'slug',
            'terms' => $_GET['genre-filter'],
            'operator' => 'IN', // Указываем, что нужно учитывать любой из выбранных жанров
        );
    }
    if (isset($_GET['country-filter']) && is_array($_GET['country-filter'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'countries',
            'field' => 'slug',
            'terms' => $_GET['country-filter'],
            'operator' => 'IN',
        );
    }
    if (isset($_GET['actor-filter']) && is_array($_GET['actor-filter'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'actors',
            'field' => 'slug',
            'terms' => $_GET['actor-filter'],
            'operator' => 'IN',
        );
    }

    // Добавляем условия для мета-полей
    if (isset($_GET['price-from']) && !empty($_GET['price-from'])) {
        $args['meta_query'][] = array(
            'key' => 'cost',
            'value' => $_GET['price-from'],
            'type' => 'numeric',
            'compare' => '>=',
        );
    }
    if (isset($_GET['price-to']) && !empty($_GET['price-to'])) {
        $args['meta_query'][] = array(
            'key' => 'cost',
            'value' => $_GET['price-to'],
            'type' => 'numeric',
            'compare' => '<=',
        );
    }
    if (isset($_GET['date-from']) && !empty($_GET['date-from'])) {
        $args['meta_query'][] = array(
            'key' => 'release_date',
            'value' => $_GET['date-from'],
            'type' => 'DATE',
            'compare' => '>=',
        );
    }
    if (isset($_GET['date-to']) && !empty($_GET['date-to'])) {
        $args['meta_query'][] = array(
            'key' => 'release_date',
            'value' => $_GET['date-to'],
            'type' => 'DATE',
            'compare' => '<=',
        );
    }
    
    if (isset($_GET['sort-by']) && isset($_GET['sort-order'])) {
        $args['orderby'] = $_GET['sort-by'];
        $args['order'] = $_GET['sort-order'];
    }

    $movies_query = new WP_Query($args);

    ob_start(); // Запускаем буферизацию вывода

    if ($movies_query->have_posts()) {
        echo '<ul>'; // Начинаем список фильмов
        while ($movies_query->have_posts()) {
            $movies_query->the_post();
            ?>
            <li>
                <h3><a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h3>
                <?php the_content(); ?>
                <p>Стоимость: $<?php echo get_post_meta(get_the_ID(), 'cost', true); ?></p>
                <p>Дата выхода: <?php echo date('Y', strtotime(get_post_meta(get_the_ID(), 'release_date', true))); ?></p>
                <p>Жанры: <?php the_terms(get_the_ID(), 'genres', ', ', ' '); ?></p>
                <p>Страны: <?php the_terms(get_the_ID(), 'countries', ', ', ' '); ?></p>
                <p>Актеры: <?php the_terms(get_the_ID(), 'actors', ', ', ' '); ?></p>
            </li>
            <?php
        }
        echo '</ul>'; // Завершаем список фильмов
        wp_reset_postdata();
    } else {
        echo 'Фильмы не найдены.';
    }

    $output = ob_get_clean(); // Получаем содержимое буфера и очищаем его
    echo $output; // Выводим содержимое

    die();
}
