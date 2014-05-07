
////////////
// MODAL BASICS
////////////

// Open a modal
function openModal(url) {
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
    });
}

// Open modal
$vic(document).on('click', 'a.vic-hover-widget, a[data-toggle="vic-modal"]', function(event) {
    event.preventDefault();
    openModal($vic(this).attr('href'));
});

// Close a modal
function closeModal() {
    $vic('#vic-modal').remove();
    $vic('.vic-creating').removeClass('vic-creating');
}

//Code to close the modal by tapping esc
//This code should not be there because the twitter bootstrap modal system
//provides such a feature but it doesn't works as well
$vic(document).on('keyup', function(e) {
  if (e.keyCode == 27) { closeModal(); }
});

// Close modal
$vic(document).on('click', '.vic-modal *[data-modal="close"]', function(event) {
    event.preventDefault();
    closeModal();
});
// END MODAL BASICS
//
