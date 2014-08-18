$vic(document).on('click', '#vic-menu-leftnavbar-trigger', function(event) {
    event.preventDefault();
    $vic('#vic-menu-leftnavbar-trigger').toggleClass('is-in');
    $vic('.vic-menu-leftnavbar').toggleClass('is-in');
    $vic('body').toggleClass('leftNavbar-in'); //used to adapt vic-modal style
});
