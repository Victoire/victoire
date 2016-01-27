$vic(document).on('submit','form[data-toggle="vic-confirm"]',function(e){
    vicSmartConfirm(e,this,'form');
});

$vic(document).on('click','a[data-toggle="vic-confirm"]',function(e){
    vicSmartConfirm(e,this,'a');
});

/**
 * This function call UIController::confirm to get a modal box to confirm the user's choice
 * @param  Event e       The click or submit event
 * @param  DomElement referer The a or submit button
 * @param  string type    a (means link) or form
 */
function vicSmartConfirm(e,referer,type){
    if($vic(referer).data('vic-confirm-field-id') != undefined && $vic('#'+$vic(referer).data('vic-confirm-field-id')).attr('checked') == "checked" ){
        return true;
    }
    e.preventDefault();
    $vic(referer).addClass('vic-confirm-waiting');
    loading(true);

    var params = {
        title: $vic(referer).data('title'),
        body: $vic(referer).data('body'),
        type: type,
        _locale: locale
    };

    if ($vic(referer).data('confirm-callback') != undefined) {
        params.confirm_callback = $vic(referer).data('confirm-callback');
    }

    if ($vic(referer).data('cancel-button-class') != undefined) {
        params.cancel_button_class = $vic(referer).data('cancel-button-class');
    }

    if ($vic(referer).data('id') != undefined) {
        params.id = $vic(referer).data('id');
    }

    $vic.ajax(
        {
            url: Routing.generate('victoire_core_ui_confirm', params),
            context: document.body,
            type: "POST",
            error: function(jsonResponse) {
                error('Woups. La panne !');
            },
            success: function(data) {
                $vic("body").append(data);
                loading(false);
            }
        }
    );
}
