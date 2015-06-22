// Event
////////


// Open select when click on dropdown link
$vic(document).on('focus', '.vic-new-widget select', function(event) {
    $vic(this).addClass('vic-open');
});
$vic(document).on('blur', '.vic-new-widget select', function(event) {
    $vic(event.target).prop('selectedIndex', 0);
    $vic(event.target).removeClass('vic-open');
});

// Create modal for new widget
$vic(document).on('change', '.vic-new-widget select', function(event) {
    event.preventDefault();
    var url = generateNewWidgetUrl(event.target);
    $vic(event.target).blur();
    openModal(url);
    $vic(this).parents('.vic-new-widget').first().addClass('vic-creating');
});


// Create new widget after submit
$vic(document).on('click', '.vic-widget-modal *[data-modal="create"]', function(event) {
    event.preventDefault();
    // we remove the prototype picker to avoid persist it
    if ($vic("select.picker_entity_select").length != 0 && $vic("select.picker_entity_select").attr('name').indexOf('[items][__name__][entity]') !== -1) {
        $vic("select.picker_entity_select").remove();
    }
    //we look for the form currently active and visible
    var form = $vic(this).parents('.vic-modal-content').find('.vic-tab-pane.vic-active form').filter(":visible");
    $vic(form).trigger("victoire_widget_form_create_presubmit");

    loading(true);

    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize()
    }).done(function(response){
        if (true === response.success) {
            if (response.hasOwnProperty("redirect")) {
                window.location.replace(response.redirect);
            } else {
                closeModal();
                $vic('.vic-creating').after(response.html);
                var slot = $vic('.vic-creating').parents('vic-slot').first();
                var slotId = $vic(slot).data('name');
                slideTo($vic('> .vic-anchor', '#vic-widget-' + response.widgetId + '-container'));
                if(typeof(Storage) !== "undefined") {
                    localStorage.setItem('victoire__widget__html__' + response.widgetId, response.html);
                }
                congrat(response.message, 10000);
            }

            loading(false);

        } else {
            warn(response.message, 10000);
            //inform user there have been an error
            if (response.html) {
                $vic('.vic-modal-body .vic-container .vic-tab-pane.vic-active').html(response.html);
            }
        }
    }).fail(function(response) {
        console.log(response);
        error('Oups, une erreur est apparue', 10000);
    });
    $vic(form).trigger("victoire_widget_form_create_postsubmit");
});


// Create new widget after submit
$vic(document).on('click', '.vic-widget-modal a[data-modal="update"]', function(event) {
    event.preventDefault();

    // we remove the prototype picker to avoid persist it
    if ($vic("select.picker_entity_select").length != 0 && $vic("select.picker_entity_select").attr('name').indexOf('appventus_victoirecorebundle_widgetlistingtype[items][__name__][entity]') !== -1) {
        $vic("select.picker_entity_select").remove();
    }
    var form = $vic(this).parents('.vic-modal-content').find('form.vic-form-active');
    if ($vic(form).length == 0) {
        form = $vic(this).parents('.vic-modal-content').find('.vic-tab-pane.vic-active form').filter(":visible");
    }
    $vic(form).trigger("victoire_widget_form_update_presubmit");

    loading(true);
    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize()
    }).done(function(response){
        if (true === response.success) {
            if (response.hasOwnProperty("redirect")) {
                window.location.replace(response.redirect);
            } else {
                closeModal();
                $vic(".vic-widget", '#vic-widget-' + response.widgetId + '-container').replaceWith(response.html);
                slideTo($vic('> .vic-anchor', '#vic-widget-' + response.widgetId + '-container'));
                congrat(response.message, 10000);
            }
            if(typeof(Storage) !== "undefined") {
                localStorage.setItem('victoire__widget__html__' + response.widgetId, response.html);
            }
            loading(false);
        } else {

            //inform user there have been an error
            warn(response.message, 10000);

            if (response.html) {
                $vic(form).parent('div').html(response.html);
            }
        }
    }).fail(function(response) {
        console.log(response);
        error('Oups, une erreur est apparue', 10000);
    });
    $vic(form).trigger("victoire_widget_form_update_postsubmit");
});

// Delete a widget after submit
$vic(document).on('click', '.vic-widget-modal a[data-modal="delete"], .vic-hover-widget-unlink', function(event) {
    //Check that there isn't a data-toggle="vic-confirm" on it !
    if ($vic(event.target).hasClass('vic-confirmed')) {
        event.preventDefault();
        $vic(document).trigger("victoire_widget_delete_presubmit");

        loading(true);
        $vic.ajax({
            type: "GET",
            url : $vic(this).attr('href')
        }).done(function(response) {
            if (true === response.success) {
                if (response.hasOwnProperty("redirect")) {
                    window.location.replace(response.redirect);
                } else {
                    //selector for the widget div
                    var widgetContainerSelector = 'vic-widget-' + response.widgetId + '-container';
                    var widgetDiv               = $vic("#" + widgetContainerSelector);
                    var widgetSlot              = $vic(widgetDiv).parents('.vic-slot').first();
                    var slotId                  = $vic(widgetSlot).data('name');
                    var slotFunction            = "updateSlotActions" + slotId;

                    //remove the div
                    widgetDiv.remove();

                    //close the modal
                    eval(slotFunction + "()");
                    closeModal();
                }
                loading(false);
            } else {
                //log the error
                console.info('An error occured during the deletion of the widget.');
                console.log(response.message);
            }
        });
        $vic(document).trigger("victoire_widget_delete_postsubmit");
    }
});

function generateNewWidgetUrl(select){
    var slotId = $vic(select).parents('.vic-slot').first().data('name');
    var container = $vic(select).parents('.vic-new-widget');
    var positionReference = 0;

    if (!$vic(container).is(':first-child')) {
        positionReference = parseInt($vic(container).prev().data('id'));
    }

    return Routing.generate(
        'victoire_core_widget_new',
        {
            'viewReference'    : viewReferenceId,
            'type'             : $vic(select).val(),
            'slot'             : slotId,
            'positionReference': positionReference,
            '_locale'          : locale
        }
    );
}
