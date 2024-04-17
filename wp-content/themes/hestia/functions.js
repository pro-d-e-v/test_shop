jQuery(document).ready(function($) {
    // AJAX для фильтрации и сортировки
    $('#filter-form').submit(function(event) {
        event.preventDefault(); // Предотвращаем отправку формы по умолчанию

        var filterData = $(this).serialize(); // Получаем данные формы

        $.ajax({
            url: movies_ajax_object.ajax_url,
            type: 'GET',
            data: filterData + '&action=filter_movies', // Добавляем действие filter_movies
            success:function(data) {
                $('#movies-container').html(data);
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    });
});