<?php
add_action('wp_ajax_filter_movies', 'filter_movies');
add_action('wp_ajax_nopriv_filter_movies', 'filter_movies');

function filter_movies()
{
    // Получаем данные из AJAX запроса
    $args = $_POST['args'];

    // Дополнительные параметры для WP_Query
    $args['post_type'] = 'movies';
    $args['posts_per_page'] = -1;

    // Создаем новый WP_Query с полученными аргументами
    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            // Выводим фильмы как в shop.php
            // (остальной код для вывода фильмов здесь)
        endwhile;
        wp_reset_postdata();
    else :
        echo 'No movies found.';
    endif;

    die();
}