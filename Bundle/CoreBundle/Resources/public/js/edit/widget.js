// Event
////////


// Open select when click on dropdown link
$vic(document).on('focus', '.v-slot select', function(event) {
    $vic(this).parent('.v-slot').addClass('v-slot--open');
});
$vic(document).on('blur', '.v-slot select', function(event) {
    $vic(event.target).parent('.v-slot').removeClass('v-slot--open');
    $vic(event.target).prop('selectedIndex', 0);
});

// Create modal for new widget
$vic(document).on('change', '.v-slot select', function(event) {
    event.preventDefault();
    var url = generateNewWidgetUrl(event.target);
    $vic(this).blur();
    openModal(url);
    $vic(this).parents('.v-slot').first().addClass('vic-creating');
});

$vic(document).on('click', '.vic-widget-modal a[data-modal="update"], .vic-widget-modal a[data-modal="create"]', function(event) {
    event.preventDefault();
    // we remove the prototype picker to avoid persist it
    if ($vic("select.picker_entity_select").length != 0 && $vic("select.picker_entity_select").attr('name').indexOf('appventus_victoirecorebundle_widgetlistingtype[items][__name__][entity]') !== -1) {
        $vic("select.picker_entity_select").remove();
    }

    var forms = [];
    $vic('.vic-tab-quantum').each(function(index, quantum) {
        // matches widget edit form with more than one mode available
        var activeForm = $vic(quantum).find('.vic-tab-mode.vic-active .vic-tab-pane.vic-active > form');
        // matches widget edit form with only static mode available
        if (activeForm.length == 0) {
            activeForm = $vic(quantum).find('.vic-tab-pane.vic-active form, form');
        }
        // matches widget stylize form
        if (activeForm.length == 0 && $vic(quantum).hasClass('vic-active')) {
            activeForm = $vic(quantum).find('form[name="widget_style"]');
        }
        forms = $vic.merge(forms, [activeForm]);
    });

    loading(true);
    var calls = [];
    $vic(forms).each(function (key, form) {
        $vic(form).trigger("victoire_widget_form_update_presubmit");
        formData = $vic(form).serialize();
        var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
        if ($vic(form).attr('enctype') == 'multipart/form-data') {
            var formData = new FormData($vic(form)[0]);
            var contentType = false;
        }
        calls.push($vic.ajax({
            type: $vic(form).attr('method'),
            url : $vic(form).attr('action'),
            data        : formData,
            processData : false,
            contentType : contentType
        }).done(function(response) {
            $vic(form).trigger("victoire_widget_form_update_postsubmit");
        })
        );
    });

    $vic.when.apply($vic, calls).then(function() {
        var errors = false;
        //arguments is a magic var that contains all callback arguments
        $vic(arguments).each(function(index, response) {
            if (response.length) {
                response = response[0];
            }
            if (false === response.success) {
                $vic('ul.vic-modal-nav-tabs a[href="#widget-'+response.widgetId+'-tab-pane"]').css('color', 'red');
                errors = true;
                //inform user there have been an error
                warn(response.message, 10000);

                if (response.html) {
                    $vic('#widget-'+response.widgetId+'-tab-pane div.vic-tab-mode.vic-tab-pane.vic-active div.vic-tab-pane.vic-active').html(response.html)

                }
            } else {
                $vic('ul.vic-modal-nav-tabs a[href="#widget-'+response.widgetId+'-tab-pane"]').css('color', '');
            }
        });
        if (errors === false) {
            window.location.reload();
        }
        loading(false);
    });
});

// Delete a widget after submit
$vic(document).on('click', 'a#widget-new-tab', function(event) {
    event.preventDefault();
    loading(true);
    var url = Routing.generate('victoire_core_widget_new', {
        type: $vic(this).data('type'),
        viewReference: $vic(this).data('viewreference'),
        slot: $vic(this).data('slot'),
        position: $vic(this).data('position'),
        parentWidgetMap: $vic(this).data('parentwidgetmap'),
        quantum: $vic(this).parents('ul').children('li').length,
        _locale: locale
    });
    $vic.ajax({
        type: "GET",
        url : url
    }).done(function(response) {
        if (true === response.success) {
            $vic('.vic-modal-tab-content-container .vic-tab-quantum').removeClass('vic-active');
            $vic('.vic-modal-nav-tabs li').removeClass('vic-active');
            var form = $vic(response.html).find('.vic-tab-quantum').first();
            var tab = $vic(response.html).find('.vic-modal-nav-tabs li:not(.widget-new-tab)').last();
            $vic('li.widget-new-tab').before(tab);
            $vic('div.vic-modal-tab-content-container').append(form);
            $vic('#victoire-modal-label').html($vic(response.html).find('li:not(.widget-new-tab) .quantum-tab-name').last().data('tabName'));
            loading(false);
        }
    });
    $vic(document).trigger("victoire_widget_delete_postsubmit");
});
// Delete a widget after submit
$vic(document).on('click', '.vic-widget-modal a.vic-confirmed, .vic-hover-widget-unlink', function(event) {
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
                    window.location.reload();
                }
                loading(false);
            } else {
                //log the error
                console.info('An error occured during the deletion of the widget.');
                console.log(response.message);
            }
        });
        $vic(document).trigger("victoire_widget_delete_postsubmit");
});


$vic(document).on('click', '.quantum-tab-name', function(event) {
    $vic('#victoire-modal-label').html($vic(this).data('tabName'));

});
$vic(document).on('click', '.quantum-tab-name i.edit', function(event) {
    event.preventDefault();
    var value = $vic('#'+$vic(this).parents('a').first().prop('id')+"-pane").find('input[name$="[quantum]"]').val();
    var input = '<input class="quantum-edit-field" type="text" value="'+value+'"></input>';
    $vic(this).parent().children('span').first().html(input);
    $vic(input).focus();

});
$vic(document).on('focusout', '.quantum-edit-field', function(event) {
    event.preventDefault();
    var value = $vic(this).val();
    $vic('#'+$vic(this).parents('a').first().prop('id')+"-pane").find('input[name$="[quantum]"]').val(value);
    $vic(this).parent().prepend('<span>'+value+'</span>');
    $vic(this).remove();

});
function generateNewWidgetUrl(select){
    var slotId = $vic(select).parents('.vic-slot').first().data('name');
    var container = $vic(select).parents('new-widget-button');

    var position = $vic(container).attr('position');
    var parentWidgetMap = $vic(container).attr('widget-map');

    var params = {
        'viewReference'    : viewReferenceId,
        'type'             : $vic(select).val(),
        'slot'             : slotId,
        '_locale'          : locale
    };

    if (position) {
        params['position'] = position;
    }
    if (parentWidgetMap) {
        params['parentWidgetMap'] = parentWidgetMap;

    }
    return Routing.generate(
        'victoire_core_widget_new',
        params
    );
}
