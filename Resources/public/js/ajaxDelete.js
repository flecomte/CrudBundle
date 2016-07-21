$(document).on('submit', 'form[name=delete], form[name=restore]', function (e) {
    e.preventDefault();

    $.ajax({
        url: $(this).attr('action'),
        type: $(this).attr('method'),
        data: $(this).serialize(),
        dataType: 'html'
    }).success(function (data, textStatus, jqXHR) {
        $('.row.entities').html(data);
    });

    return false;
});