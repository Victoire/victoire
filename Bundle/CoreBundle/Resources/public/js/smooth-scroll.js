$(document).on('click', '[data-scroll="smooth"]', function(e) {
    e.preventDefault();
    $.scrollTo(this.hash, 1500);
});
