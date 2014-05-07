

// PAGE MODAL EVENTS
// Create new page after submit
$(document).on('click', '.vic-modal.vic-page-modal *[data-modal="create"]', function(event) {
    event.preventDefault();
    var form = $(this).parents('.vic-modal-content').find('.vic-tab-pane.vic-active form');;

    $.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize(),
    }).done(function(response){
        if (true === response.success) {
            //redirect to the new page
            window.location.replace(response.url);
            closeModal();
        } else {
            $vic('#vic-modal').replaceWith(response.html);
            $vic('#vic-modal').vicmodal({
                keyboard: true,
                backdrop: false
            });
        }
    });
});

// Update an existing page
$(document).on('click', '.vic-modal.vic-page-modal a[data-modal="update"]', function(event) {
    event.preventDefault();
    var form = $(this).parents('.vic-modal-content').find('.vic-tab-pane.vic-active form');

    $.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize(),
    }).done(function(response){
        if (true === response.success) {
            //@todo Use AvAlertify to warn user that the action succeed
            window.location.replace(response.url);
            closeModal();
        } else {
            $('#vic-modal').replaceWith(response.html);
            $('#vic-modal').vicmodal({
                keyboard: true,
                backdrop: false
            });
        }
    });
});

// Create new page after submit
$(document).on('click', '.vic-modal.vic-page-modal a[data-modal="delete"]', function(event) {

    event.preventDefault();
    if (!confirm('Action dangereuse, Vous allez supprimer la page. Vous confirmez ?')) {
        return false;
    };

    $.ajax({
        type: "GET",
        url : $(this).attr('href')
    }).done(function(response){
        if (true === response.success) {
            //redirect to the new page
            window.location.replace(response.url);
        } else if (false === response.success) {
            //@todo Use AvAlertify to warn user that the action failed
            alert(response.message);
        }
    });
});
