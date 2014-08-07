$vic(document).on('change', '#vic-switcher-editMode', function(event) {
    var val = $vic(this).is(':checked');

    if (val == false) {
        $vic('body').removeAttr('role');
        var action = 'disable';
    } else {
        $vic('body').attr('role','admin');
        var action = 'enable';
    }

    $vic.ajax({
        url: Routing.generate('victoire_core_switch', {'mode': action }),
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
