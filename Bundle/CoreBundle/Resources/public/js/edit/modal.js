
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
        //remove the previous instance of the modal
        $vic('#vic-modal').remove();
        //add the html of the modal
        $vic('body').append(response.html);
        //display the modal
        $vic('#vic-modal').vicmodal({
            keyboard: true,
            backdrop: false
        });
        loading(false);
        $vic(document).trigger('victoire_modal_open_after');
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
$vic(document).on('click', 'a.vic-hover-widget', function(event) {
    event.preventDefault();

    var role = $vic('body').attr('role');
    if (typeof role !== typeof undefined && role !== false) {
        if (role == "admin-style") {
            var route = 'victoire_core_widget_stylize';
        } else if (role == "admin-edit") {
            var route = 'victoire_core_widget_edit';
        }

        var id = $vic(this).parents('.vic-widget-container').first().data('id');
        var url = Routing.generate(route, {'id': id, 'viewReference': viewReferenceId});
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
        modal = $vic('.vic-modal.vic-in').last();
    }

    $vic(modal).vicmodal('hide');
    setTimeout(function() {$vic('.vic-creating').removeClass('vic-creating');}, 10);
}

//Code to close the modal by tapping esc
//This code should not be there because the twitter bootstrap modal system
//provides such a feature but it doesn't works as well
$vic(document).on('keyup', function(e) {
    if (e.keyCode == 27) {
        if (!$vic('body').hasClass('page-unloading')) {
            closeModal($vic('.vic-modal').last());
        } else {
            $vic('body').removeClass('page-unloading');
        }
    }
});

// Close modal
$vic(document).on('click', '.vic-modal *[data-modal="close"]', function(event) {
    event.preventDefault();
    modal = $vic(event.target).parents('.vic-modal');
    closeModal(modal);
});
// END MODAL BASICS
//
