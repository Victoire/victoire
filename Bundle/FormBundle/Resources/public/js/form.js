/**
 * Refresh on change
 ==================
 * Problematic:
 * When a form field conditions the others fields, add the data-refreshOnChange="true" attribute
 * to pass here and then reload|refresh the template (without any errors because the form is not yet properly submitted)
 * by calling the action in a custom mode: novalidate.
 * Then, on the change event, it will call the form action with the novalidate GET variable.
 * In the WidgetManager@createWidget and WidgetManager@editWidget, we do not validate the form and just returninng the template
 * with the new form types.
 *
 **/
$vic(document).on('change', 'select[data-refreshOnChange="true"], input:checkbox[data-refreshOnChange="true"]', function(event) {
    var form = $(this).parents('form');
    loading(true);
    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action') + '?novalidate',
        data: form.serialize(),
        async: true,
    }).done(function(response){
        $vic('.vic-modal-body .vic-container-fluid .vic-tab-pane.vic-active').html(response.html);
        eval($vic('.vic-modal-body .vic-container-fluid .vic-tab-pane.vic-active').find("script").text());
        loading(false);
    }).fail(function(response) {
        console.log(response);
        error('Oups, une erreur est apparue', 10000);
    });
});
