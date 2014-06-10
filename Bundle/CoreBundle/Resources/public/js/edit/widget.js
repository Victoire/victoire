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
    if ($("select.picker_entity_select").length != 0 && $("select.picker_entity_select").attr('name').indexOf('[items][__name__][entity]') !== -1) {
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
    }).done(function(response) {
        if (true === response.success) {
            //selector for the widget div
            var widgetContainerSelector = 'vic-widget-' + response.widgetId + '-container';
            var widgetDiv = $vic("#" + widgetContainerSelector);
            //remove the div
            widgetDiv.remove();
            //close the modal
            closeModal();
        } else {
            //log the error
            console.log('An error occured during the deletion of the widget.');
            console.log(response.message);
        }
    });
});

//create the victoire object
if (victoire === undefined) {
    var victoire = {};    
}

$vic(function() {
    //Add the widget namespace
    victoire.widget = {};
    
    //add properties
    victoire.widget.setMode = function (link, modeValue)
    {
        //get the pane that is 3 parents up
        var paneDiv = $vic(link).parent().parent().parent();
        //get the form
        var form = paneDiv.children('form');
        //get the mode input hidden        
        var mode = form.children().children("[name$='[mode]']");
        
        //set the new value
        mode.val(modeValue);       
    };
});
