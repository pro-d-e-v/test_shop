<?php
/**
 * The template for displaying single movie posts
 *
 * @package Hestia
 * @since Hestia 1.0
 */

get_header();

do_action( 'hestia_before_single_post_wrapper' );
?>

<div class="<?php echo hestia_layout(); ?>">
    <div class="blog-post blog-post-wrapper">
        <div class="container">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <h1 class="entry-title"><?php the_title(); ?></h1>
                        </header><!-- .entry-header -->

                        <div class="entry-content">
                            <?php
                            // Выводим стоимость фильма
                            $price = get_field('cost');
                            if ($price) {
                                echo '<p><strong>Стоимость:</strong> $' . $price . '</p>';
                            }

                            // Выводим дату релиза фильма
                            $release_date = get_field('release_date');
                            if ($release_date) {
                                echo '<p><strong>Дата релиза:</strong> ' . $release_date . '</p>';
                            }

                            // Выводим жанры фильма
                            echo get_the_term_list(get_the_ID(), 'genres', '<p><strong>Жанры:</strong> ', ', ', '</p>');

                            // Выводим страны фильма
                            echo get_the_term_list(get_the_ID(), 'countries', '<p><strong>Страны:</strong> ', ', ', '</p>');

                            // Выводим актеров фильма
                            echo get_the_term_list(get_the_ID(), 'actors', '<p><strong>Актеры:</strong> ', ', ', '</p>');

                            // Выводим содержимое поста
                            the_content();

                            // Выводим ссылки на следующий и предыдущий посты
                            //the_post_navigation();
                            ?>
                        </div><!-- .entry-content -->
                    </article><!-- #post-<?php the_ID(); ?> -->
                    <?php
                endwhile;
            else :
                // Если нет постов
                get_template_part( 'template-parts/content', 'none' );
            endif;
            ?>
            <?php echo shortcode_exists('cptwooint_cart_button') ? do_shortcode( "[cptwooint_cart_button/]" ) : '' ; ?>
        </div>
    </div>
</div>
<style>
.quantity {
    display: none;
}
</style>
<?php
if ( ! is_singular( 'elementor_library' ) ) {
    do_action( 'hestia_blog_related_posts' );
}
?>
<?php get_footer(); ?>