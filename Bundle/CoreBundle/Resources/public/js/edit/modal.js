
////////////
// MODAL BASICS
////////////

// Open a modal
function openModal(url) {
    $vic(document).trigger('victoire_modal_open_before');
    loading(true);
    $vic.ajax({
        type: "GET",
        url: url
    }).done(function(response){
        if (false === response.success) {
            warn(response.message, 10000);
        } else {
            //remove drops from the previous instance of the modal
            $vic('#vic-modal [data-flag*="v-drop"]').each(function(index, el) {
                $vic($vic(el).attr('data-droptarget')).remove();
            });

            //remove the previous instance of the modal
            $vic('#vic-modal').remove();
            //add the html of the modal
            $vic('body').append(response.html);
            //display the modal
            $vic('#vic-modal').vicmodal({
                keyboard: true,
                backdrop: false
            });
            $vic('#vic-modal').attr('data-modal', 'show');

            // set FAB on open mode
            $vic('#v-float-container .v-btn--fab').addClass('v-btn--fab-open');
        }
        loading(false);
        $vic(document).trigger('victoire_modal_open_after');
    }).fail(function() {
        loading(false);
    });

    return $vic('#vic-modal');
}

//redirect to the direct links of the menu
//the dropdown menu intercepts the event and does not let the link do their job
//@todo the vic none is a fix
//the dropdown does an event prevent that does not allow the link.
$vic(document).on('click', 'a[data-toggle="vic-none"]', function(event) {
    event.preventDefault();
    window.location = $vic(this).attr('href');
});


// Open modal
$vic(document).on('click', '.v-widget__overlay', function(event) {
    event.preventDefault();

    var role = $vic('body').attr('role');
    if (typeof role !== typeof undefined && role !== false) {
        if (role == "admin-style") {
            var route = 'victoire_core_widget_stylize';
        } else if (role == "admin-edit") {
            var route = 'victoire_core_widget_edit';
        }

        var id = $vic(this).parents('.vic-widget-container').first().data('id');
        var url = Routing.generate(route, {'id': id, 'viewReference': viewReferenceId, _locale: locale});
        openModal(url);
    } else {
        console.error('You only should click on this in edit or style mode !');
    }
});

// Open modal
$vic(document).on('click', 'a[data-toggle="vic-modal"]', function(event) {
    event.preventDefault();
    openModal($vic(this).attr('href'));
});

// Close a modal
function closeModal(modal) {
    if (modal == undefined) {
        modal = $vic('.v-modal[data-modal="show"]').last();
    }

    $vic(modal).attr('data-modal', 'hidden');

    // set FAB on normal mode
    $vic('#v-float-container .v-btn--fab').removeClass('v-btn--fab-open');

    setTimeout(function() {$vic('.vic-creating').removeClass('vic-creating');}, 10);
}


//Code to close the modal by tapping esc
//This code should not be there because the twitter bootstrap modal system
//provides such a feature but it doesn't works as well
$vic(document).on('keyup', function(e) {
    if (e.keyCode == 27) {
        if (!$vic('body').hasClass('page-unloading')) {
            closeModal($vic('.v-modal').last());
        } else {
            $vic('body').removeClass('page-unloading');
        }
    }
});

// Close modal
$vic(document).on('click', '.v-modal [data-modal="close"]', function(event) {
    event.preventDefault();
    modal = $vic(event.target).parents('.v-modal');
    closeModal(modal);
});
// END MODAL BASICS
//
