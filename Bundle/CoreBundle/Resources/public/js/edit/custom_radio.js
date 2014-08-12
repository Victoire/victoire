$vic(document).on('change', '#vic-switcher-editMode', function(event) {
    var mode = $vic(this).is(':checked');

    if (mode == false) {
        $vic('body').removeAttr('role');
    } else {
        $vic('body').attr('role','admin');
    }

    $vic.ajax({
        url: Routing.generate('victoire_core_switch', {'mode': mode.toString() }),
        context: document.body,
        type: "GET",
        error: function(jsonResponse) {
            if (typeof toastr === 'undefined') {
                alert("Il semble s'être produit une erreur");
            } else {
                toastr.options = {
                    "positionClass": "toast-bottom-left",
                }

                toastr.error("Il semble s'être produit une erreur");
          }
          $vic('#canvasloader-container').fadeOut();
      }
  });
});
