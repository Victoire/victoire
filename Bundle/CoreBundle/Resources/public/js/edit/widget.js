// Event
////////

// Create modal for new widget
$vic(document).on('click', '.vic-new-widget > .vic-dropdown-menu a', function(event) {
    event.preventDefault();
    openModal($vic(this).attr('href'));
    $vic(this).closest('.vic-dropdown.vic-new-widget').addClass('vic-creating');
});


// Create new widget after submit
$vic(document).on('click', '.vic-widget-modal *[data-modal="create"]', function(event) {
    event.preventDefault();
    // we remove the prototype picker to avoid persist it
    if ($("select.picker_entity_select").length != 0 && $("select.picker_entity_select").attr('name').indexOf('appventus_victoirecorebundle_widgetlistingtype[items][__name__][entity]') !== -1) {
        $("select.picker_entity_select").remove();
    }
    var form = $vic(this).parents('.vic-modal-content').find('.vic-tab-pane.vic-active form');;

    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize(),
    }).done(function(response){
        if (true === response.success) {
            if ($vic('.vic-creating').hasClass('vic-first')) {
                $vic('.vic-creating').after(response.html);
            } else {
                $vic('.vic-creating').parents('.widget-container').after(response.html);
            }
            closeModal();
            
            //save the positions of the widgets
            updatePosition();
        } else {
            $vic('.vic-modal-body .vic-container').html(response.html);
        }
    });
});


// Create new widget after submit
$vic(document).on('click', '.vic-widget-modal a[data-modal="update"]', function(event) {
    event.preventDefault();

    // we remove the prototype picker to avoid persist it
    if ($("select.picker_entity_select").length != 0 && $("select.picker_entity_select").attr('name').indexOf('appventus_victoirecorebundle_widgetlistingtype[items][__name__][entity]') !== -1) {
        $("select.picker_entity_select").remove();
    }
    var form = $vic(this).parents('.vic-modal-content').find('.vic-tab-pane.vic-active form');
    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize(),
    }).done(function(response){
        if (true === response.success) {
            $vic("#"+response.widgetId).replaceWith(response.html);
            closeModal();
        } else {
            closeModal();
            $vic('body').append(response.html);
            $vic('#vic-modal').vicmodal({
                keyboard: true,
                backdrop: false
            });
        }
    });
});

// Create new widget after submit
$vic(document).on('click', '.vic-widget-modal a[data-modal="delete"]', function(event) {
    event.preventDefault();

    $vic.ajax({
        type: "GET",
        url : $vic(this).attr('href')
    }).done(function(response){
        if (true === response.success) {
            $vic("#"+response.widgetId).next('.vic-dropdown.vic-new-widget').remove();
            $vic("#"+response.widgetId).remove();
            closeModal();
        }
    });
});
