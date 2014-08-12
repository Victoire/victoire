$vic(document).on('change', '#vic-switcher-editMode', function(event) {

    if ($vic(this).is(':checked')) {
        $vic('body').attr('role','admin');
        var route = Routing.generate('victoire_core_switchEnable');
    } else {
        $vic('body').removeAttr('role');
        var route = Routing.generate('victoire_core_switchDisable');
    }

    $vic.ajax({
        url: route,
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
