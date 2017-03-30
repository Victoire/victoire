$vic(document).on('change', '[name="mode-switcher"]', function(event) {

    var mode = $('input[name="mode-switcher"]:checked').val();
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
