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

// Eval width of the slots to add or not a class
function evalSlotWidth(slot) {
    var smallClass = 'v-slot--sm';

    if (slot.offsetWidth > 250 && slot.classList.contains(smallClass)) {
        slot.classList.remove(smallClass);
    } else if (slot.offsetWidth <= 250 && slot.offsetWidth > 0) {
        slot.classList.add(smallClass);
    }

    return;
}

$vic(document).ready(function() {
    $('.v-slot').each(function(index, el) {
        return evalSlotWidth(el);
    });
});

$vic(window).resize(function() {
    $('.v-slot').each(function(index, el) {
        return evalSlotWidth(el);
    });
});


// Create modal for new widget
$vic(document).on('change', '.v-slot select', function(event) {
    event.preventDefault();
    var url = generateNewWidgetUrl(event.target);
    $vic(this).blur();
    openModal(url);
    $vic(this).parents('.v-slot').first().addClass('vic-creating');
});

$vic(document).on('click', '.v-modal--widget a[data-modal="update"], .v-modal--widget a[data-modal="create"]', function(event) {
    event.preventDefault();

    var forms = [];
    $vic('[data-group="tab-widget-quantum"]').each(function() {

        var quantumLetter = $vic(this).data('quantum');

        // matches widget stylize form
        activeForm = $vic(this).find('form[name="' + quantumLetter + '_widget_style"]');

        // matches widget edit form with more than one mode available
        if (activeForm.length == 0) {
            var activeForm = $vic(this).find('[data-group="picker-' + quantumLetter + '"][data-state="visible"] [data-flag="v-collapse"][data-state="visible"] > form');
        }

        // matches widget edit form with only static mode available
        if (activeForm.length == 0) {
            activeForm = $vic(this).find('[data-group="picker-' + quantumLetter + '"][data-state="visible"] form');
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
        }).fail(function() {
            loading(false);
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
        quantum: $vic("#v-quantum-tab > .v-flex-col").length - 1,
        _locale: locale
    });
    $vic.ajax({
        type: "GET",
        url : url
    }).done(function(response) {
        if (true === response.success) {
            $vic('[data-group="tab-widget-quantum"][data-state="visible"]').attr('data-state', 'hidden');
            $vic('.v-btn--quantum-active').removeClass('v-btn--quantum-active');
            var form = $vic(response.html).find('[data-group="tab-widget-quantum"]').first();
            var tab = $vic(response.html).find('.v-btn--quantum').last().addClass('v-btn--quantum-active').parent();
            $vic('#widget-new-tab').parent('.v-flex-col').before(tab);
            $vic('#v-modal-tab-content-container').append(form);

            loading(false);
        }
    }).fail(function() {
        loading(false);
    });
    $vic(document).trigger("victoire_widget_delete_postsubmit");
});

// Delete a widget after submit
$vic(document).on('click', '.v-modal--widget a.vic-confirmed, .vic-hover-widget-unlink', function(event) {
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
        }).fail(function() {
            loading(false);
        });
        $vic(document).trigger("victoire_widget_delete_postsubmit");
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


// update on left bar, the quantum name when modifying it
$vic(document).on('keyup', '[data-flag="v-quantum-name"]', function(event) {
    var activeQuantumTab = $vic('#v-quantum-tab > .v-flex-col .v-btn--quantum-active');

    if (!activeQuantumTab.find('span[data-flag="old-name"]').length) {
        var originalShortName = $vic('#v-quantum-tab > .v-flex-col .v-btn--quantum-active span:not([data-flag="old-name"])').text();
        activeQuantumTab.append('<span data-flag="old-name" style="display: none;">' + originalShortName + '</span>');
    }

    var quantumShortName = event.target.value.length ? event.target.value.substr(0, 2) : activeQuantumTab.find('span[data-flag="old-name"]').text();

    activeQuantumTab.find('span:not([data-flag="old-name"])').text(quantumShortName);
});
