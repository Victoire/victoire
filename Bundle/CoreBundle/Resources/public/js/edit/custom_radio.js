var toggleHandler = function(toggle) {
    var toggle = toggle;
    var radio = $vic(toggle).find("input");

    var checkToggleState = function() {
        if (radio.eq(0).is(":checked")) {
            $vic(toggle).removeClass("vic-toggle-off");
        } else {
            $vic(toggle).addClass("vic-toggle-off");
        }
    };

    checkToggleState();

    radio.each(function() {
        $vic(this).click(function() {
            $vic(toggle).toggleClass("vic-toggle-off");

            if ($vic(this).val() == 0) {
                $vic('body').removeAttr('role');
            } else {
                $vic('body').attr('role','admin');
            }

            $vic.ajax({
                url: Routing.generate('victoire_cms_switch', {'mode': $vic(this).val() }),
                context: document.body,
                type: "GET",
                error: function(jsonResponse) {
                    if (typeof toastr === 'undefined') {
                        alert("Il semble s'êre produit une erreur");
                    } else {
                        toastr.options = {
                          "positionClass": "toast-bottom-left",
                        }
                        toastr.error("Il semble s'êre produit une erreur");
                    }
                    $vic('#canvasloader-container').fadeOut();
                }
            });
        });
    });
};

$vic(document).ready(function() {
    $vic(".vic-toggle").each(function(index, toggle) {
        toggleHandler(toggle);
    });
});
