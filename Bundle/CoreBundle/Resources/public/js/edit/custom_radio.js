$vic(document).on('click', '#vic-mode-toggler .vic-mode', function(event) {

    var mode = $vic(this).data('mode');
    $vic('body').attr('role', mode);
    var route = Routing.generate('victoire_core_switchMode', {'mode': mode, '_locale': locale});
    $vic('#vic-mode-toggler .vic-mode').removeClass('vic-active');
    $vic(this).addClass('vic-active');

    loading(true);
    $vic.ajax(
        {
            url: route,
            context: document.body,
            type: "GET",
            error: function(jsonResponse) {
                error('Woups. La panne !');
            }
        }
    );
    loading(false);
});
