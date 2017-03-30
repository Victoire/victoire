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
    var input = $vic(this);
    var form = $vic(this).parents('form');
    loading(true);

    var targetClass = '.v-modal__body .v-container [data-group="tab-widget-quantum"][data-state="visible"]';
    if ($vic(this).data('target')) {
        targetClass = $vic(this).data('target');
    }
    var updateStrategy = "html";
    if ($vic(this).data('update-strategy')) {
        updateStrategy = $vic(this).data('update-strategy');
    }

    //Get last element of visible target class
    var $target = $vic(document).find(targetClass + ':visible');
    $target = $target.last();

    $vic.ajax({
        type: form.attr('method'),
        url : form.attr('action') + (form.attr('action').split('?')[1] ? '&' : '?') + 'novalidate',
        data: form.serialize(),
        async: true
    }).done(function(response){
        var actives = [];
        $vic(input).parents('.vic-active').each(function() {
            if ($vic(this).attr('id')) {
                actives.push($vic(this).attr('id'));
            }
        });
        //By default, the updateStrategy is html (a simple replace) but you can set your own function
        //for example, append, after etc or even a custom one.
        eval('$target.' + updateStrategy + '(response.html)');

        $vic(actives).each(function() {
            $vic('a[href="#'+this+'"]').victab('show');
        });
        loading(false);
    }).fail(function(response) {
        console.log(response);
        error('Oups, une erreur est apparue', 10000);
    });
});

/**
 * Keep order when eval or load script
 =====================================
 * Problematic:
 * When 2 or more scripts tags need to be evaluated and the first script is an external script
 * if the other scripts need the first one then an error occur because the first wasn't load entirely.
 * example:
 * <script src="source.js"></script>
 * <script>var var2 = CONST + 1 // CONST is defined in source.js</script>
 *
 * This function evaluate scripts one per one and wait to load entirely each script before load an other one.
 */
function evalAll(scripts, current) {
    current = (current) ? current : 0;
    if (current < scripts.length) {
        var script = $(scripts[current]);
        var src = script.attr('src');
        if (!src) {
            eval(script.text());
            evalAll(scripts, current + 1);
        }
        else {
            $.getScript(src, function(){
                evalAll(scripts, current + 1);
            });
        }
    }
}
