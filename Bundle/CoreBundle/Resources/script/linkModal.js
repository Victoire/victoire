$(document).ready(function() {
    // To use it  : <a href="/url/to/a/victoire/page?layout=modal" data-toggle="viclink-modal">link</a>
    $(document).on('click', '[data-toggle="viclink-modal"]', function(e) {
        e.preventDefault();
        $('.modal').modal('hide').on('hidden.bs.modal', function(){$(this).remove()});

        $(document).trigger('vic.modal.request');
        var url = $(this).attr('href');
        $.get(url, function(data) {
            $('body').append(data);
            $(document).trigger('vic.modal.append');
        }).success(function() {
            $(document).trigger('vic.modal.success');
        });
    });

    $('*[data-toggle="viclink-modal"]').each(function() {
        $(this).css({
            'pointer-events' : 'auto',
            'cursor' : 'auto'
        });
    });
});

$(document).ajaxComplete(function() {
    $('*[data-toggle="viclink-modal"]').each(function () {
        $(this).css({
            'pointer-events': 'auto',
            'cursor': 'auto'
        });
    });
});