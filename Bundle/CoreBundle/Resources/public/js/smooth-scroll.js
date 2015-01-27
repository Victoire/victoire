$vic(document).on('click', '[data-scroll="smooth"]', function(e) {
    e.preventDefault();
    $vic.scrollTo(this.hash, 1500);
});
