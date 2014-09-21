

// PAGE MODAL EVENTS
// Create new page after submit
$vic(document).on('click', '.vic-modal.vic-view-modal *[data-modal="create"]', function(event) {
    event.preventDefault();
    var form = $vic(this).parents('.vic-modal-content').find('form');

    loading(true);

    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize(),
    }).done(function(response){
        if (true === response.success) {
            //redirect to the new page
            window.location.replace(response.url);
            closeModal();
            congrat(response.message, 10000);
        } else {
            warn(response.message, 10000);
            $vic('#vic-modal').replaceWith(response.html);
            $vic('#vic-modal').vicmodal({
                keyboard: true,
                backdrop: false
            });
        }
    });
});

// Update an existing page
$vic(document).on('click', '.vic-modal.vic-view-modal a[data-modal="update"]', function(event) {
    event.preventDefault();
    var form = $vic(this).parents('.vic-modal-content').find('form');

    loading(true);
    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize(),
    }).done(function(response){
        loading(false);
        if (true === response.success) {
            //@todo Use AvAlertify to warn user that the action succeed
            window.location.replace(response.url);
            closeModal();
            congrat(response.message, 10000);
        } else {
            warn(response.message, 10000);
            $vic('#vic-modal').replaceWith(response.html);
            $vic('#vic-modal').vicmodal({
                keyboard: true,
                backdrop: false
            });
        }
    });
});

// Create new page after submit
$vic(document).on('click', '.vic-modal.vic-view-modal a[data-modal="delete"]', function(event) {
    //Check that there isn't a data-toggle="vic-confirm" on it !
    if ($vic(event.target).data('toggle') != "vic-confirm" || $vic(event.target).hasClass('vic-confirmed')) {
        event.preventDefault();
        loading(true);

        $vic.ajax({
            type: "GET",
            url : $vic(this).attr('href')
        }).done(function(response){
            if (true === response.success) {
                //redirect to the new page
                window.location.replace(response.url);
                congrat(response.message, 10000);
            } else {
                warn(response.message, 10000);
                alert(response.message);
            }
        });
    }
});
