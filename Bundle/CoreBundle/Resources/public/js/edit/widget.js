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
        data: form.serialize(),
    }).done(function(response){
        if (true === response.success) {
            if ($vic('.vic-creating').hasClass('vic-first')) {
                $vic('.vic-creating').after(response.html);
            } else {
                $vic('.vic-creating').parents('.vic-widget-container').after(response.html);
            }
            closeModal();
            loading(false);

            //save the positions of the widgets
            updatePosition();
            congrat(response.message, 10000);
        } else {
            warn(response.message, 10000);
            //inform user there have been an error
            if (response.html) {
                $vic('.vic-modal-body .vic-container').html(response.html);
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
    var form = $vic(this).parents('.vic-modal-content').find('.vic-tab-pane.vic-active form').filter(":visible");
    $vic(form).trigger("victoire_widget_form_update_presubmit");

    loading(true);
    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action'),
        data: form.serialize(),
    }).done(function(response){
        if (true === response.success) {
            $vic("#"+response.widgetId).html(response.html);
            closeModal();
            loading(false);
            congrat(response.message, 10000);
        } else {

            //inform user there have been an error
            warn(response.message, 10000);


            if (response.html) {
                $vic(form).parent('div').replaceWith(response.html);
            }
        }
    }).fail(function(response) {
        console.log(response);
        error('Oups, une erreur est apparue', 10000);
    });
    $vic(form).trigger("victoire_widget_form_update_postsubmit");
});

// Delete a widget after submit
$vic(document).on('click', '.vic-widget-modal a[data-modal="delete"]', function(event) {
    //Check that there isn't a data-toggle="vic-confirm" on it !
    if ($vic(event.target).data('toggle') != "vic-confirm" || $vic(event.target).hasClass('vic-confirmed')) {
        event.preventDefault();
        $vic(document).trigger("victoire_widget_delete_presubmit");

        loading(true);
        $vic.ajax({
            type: "GET",
            url : $vic(this).attr('href')
        }).done(function(response) {
            if (true === response.success) {
                //selector for the widget div
                var widgetContainerSelector = 'vic-widget-' + response.widgetId + '-container';
                var widgetDiv = $vic("#" + widgetContainerSelector);
                var widgetSlot = $vic(widgetDiv).parents('.vic-slot');
                var slotFunction = "updateSlotActions" + $vic(widgetSlot).data('name');

                //remove the div
                widgetDiv.remove();

                //close the modal
                eval(slotFunction + "()");
                closeModal();
                loading(false);
            } else {
                //log the error
                console.log('An error occured during the deletion of the widget.');
                console.log(response.message);
            }
        });
        $vic(document).trigger("victoire_widget_delete_postsubmit");
    };
});


  //////////////////////////////////
 // New Widget dropdown position //
//////////////////////////////////

$vic(document).on('click', '[data-toggle="vic-dropdown"]', function() {
    var dropdown = $vic(this).siblings('.vic-dropdown-menu.vic-dropdown-newWidget');
    var dropdownWidth = dropdown.outerWidth();
    var dropdownHeight = dropdown.outerHeight();

    dropdown.css({
        marginTop: -0.5 * dropdownHeight,
        marginLeft: -0.5 * dropdownWidth,
    });
});
