var search = function ($form)
{
    if (xhr_search != null) {
        xhr_search.abort();
    }
    $form.addClass('loading');
    $($form.data('destination')).addClass('loading');
    xhr_search = $.ajax({
        url: window.location.pathname +'?'+ $form.serialize(),
        type: 'GET',
        dataType: 'html'
    }).success(function (data, textStatus, jqXHR) {
        $($form.data('destination')).html(data);
        $form.trigger('searchFinish');
    }).complete(function () {
        $form.removeClass('loading');
        $($form.data('destination')).removeClass('loading');
    });
};

var xhr_search = null;
$(document).on('keyup', '.search input', function (e) {
    search($(this).parents('form'));
});
$(document).on('dp.change', '.search input', function (e) {
    search($(e.target).parents('form'));
});
$(document).on('change', '.search select, .search input', function (e) {
    search($(this).parents('form'));
});
$(function () {
    $('.search button[type=submit]').hide();
});
