
////////////
// MODAL BASICS
////////////

// Open a modal
function openModal(url) {
    loading(true);
    //trick to hide the dropdown (not hidden because of the preventDefault use)
    $vic('.vic-dropdown').removeClass('vic-open');
    $vic.ajax({
        type: "GET",
        url: url
    }).done(function(response){
        //remove the previous instance of the modal
        $('#vic-modal').remove();
        //add the html of the modal
        $vic('body').append(response.html);
        //display the modal
        $vic('#vic-modal').vicmodal({
            keyboard: true,
            backdrop: false
        });
        loading(false);
    });
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
$vic(document).on('click', 'a.vic-hover-widget, a[data-toggle="vic-modal"]', function(event) {
    event.preventDefault();
    openModal($vic(this).attr('href'));
});

// Close a modal
function closeModal(modal) {
    if (modal == undefined) {
        modal = $vic('.vic-modal.vic-in').last();
    }

    $vic(modal).vicmodal('hide');
    $vic('.vic-creating').removeClass('vic-creating');
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
