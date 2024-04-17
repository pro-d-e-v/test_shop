<?php
/*
Template Name: Список фильмов
*/

get_header();

$default         = hestia_get_blog_layout_default();
$sidebar_layout  = apply_filters( 'hestia_sidebar_layout', get_theme_mod( 'hestia_blog_sidebar_layout', $default ) );
$wrapper_classes = apply_filters( 'hestia_filter_archive_content_classes', 'col-md-8 archive-post-wrap' );

do_action( 'hestia_before_archive_content' );

// Получаем данные фильтров
$genres = get_terms( 'genres' );
$countries = get_terms( 'countries' );
$actors = get_terms( 'actors' );

// Получаем данные фильмов
$args = array(
    'post_type' => 'movies',
    'posts_per_page' => -1, // Показать все фильмы
);
$movies_query = new WP_Query( $args );
?>

<div class="<?php echo hestia_layout(); ?>">
    <div class="hestia-blogs" data-layout="<?php echo esc_attr( $sidebar_layout ); ?>">
        <div class="container">
            <div class="row">
                <section class="filters">
            <h2>Фильтры</h2>
            <!-- Форма для фильтров -->
            <form id="filter-form">
    <div class="filter-group">
        <h5>Жанр:</h5>
        <?php foreach ($genres as $genre) : ?>
            <label>
                <input type="checkbox" name="genre-filter[]" value="<?php echo esc_attr($genre->slug); ?>">
                <?php echo esc_html($genre->name); ?>
            </label>
        <?php endforeach; ?>
    </div>
    <div class="filter-group">
        <h5>Страна:</h5>
        <?php foreach ($countries as $country) : ?>
            <label>
                <input type="checkbox" name="country-filter[]" value="<?php echo esc_attr($country->slug); ?>">
                <?php echo esc_html($country->name); ?>
            </label>
        <?php endforeach; ?>
    </div>
    <div class="filter-group">
        <h5>Актер:</h5>
        <?php foreach ($actors as $actor) : ?>
            <label>
                <input type="checkbox" name="actor-filter[]" value="<?php echo esc_attr($actor->slug); ?>">
                <?php echo esc_html($actor->name); ?>
            </label>
        <?php endforeach; ?>
    </div>
    <div class="filter-group">
                    <label for="price-from">Стоимость от:</label>
                    <input type="number" id="price-from" name="price-from" min="0">
                </div>
                <div class="filter-group">
                    <label for="price-to">Стоимость до:</label>
                    <input type="number" id="price-to" name="price-to" min="0">
                </div>
                <div class="filter-group">
                    <label for="date-from">Дата от:</label>
                    <input type="date" id="date-from" name="date-from">
                </div>
                <div class="filter-group">
                    <label for="date-to">Дата до:</label>
                    <input type="date" id="date-to" name="date-to">
                </div>
                <div class="filter-group">
                    <label for="sort-by">Сортировать по:</label>
                    <select id="sort-by" name="sort-by">
                        <option value="price">Стоимость</option>
                        <option value="date">Дата выхода</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="sort-order">Направление:</label>
                    <select id="sort-order" name="sort-order">
                        <option value="asc">По возрастанию</option>
                        <option value="desc">По убыванию</option>
                    </select>
                </div>
    <button type="submit">Применить фильтры</button>
</form>

        </section>

        <section class="movies">
            <h3>Фильмы</h3>
            <div id="movies-container">
                <!-- Здесь будет отображаться список фильмов -->
                <?php if ($movies_query->have_posts()) : ?>
                    <ul>
                        <?php while ($movies_query->have_posts()) : $movies_query->the_post(); ?>
                            <li>
                                <h3><a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h3>
                                <?php the_content(); ?>
                                <p>Стоимость: $<?php echo get_post_meta(get_the_ID(), 'cost', true); ?></p>
                                <p>Дата выхода: <?php echo date('Y', strtotime(get_post_meta(get_the_ID(), 'release_date', true))); ?></p>
                                <p>Жанры: <?php the_terms(get_the_ID(), 'genres', ', ', ' '); ?></p>
                                <p>Страны: <?php the_terms(get_the_ID(), 'countries', ', ', ' '); ?></p>
                                <p>Актеры: <?php the_terms(get_the_ID(), 'actors', ', ', ' '); ?></p>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <p>Фильмы не найдены.</p>
                <?php endif; ?>
            </div>
        </section>
            </div>
        </div>
    </div>
</div>
<style>
div#movies-container ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: flex-start;
    gap: 113px;
}
</style>

<?php
get_footer(); ?>