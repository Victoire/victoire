function showSelectedLinkType(selects) {
    $vic(selects).each(function(select) {
        var element = $vic(this).val();
        var id = $vic(this).attr('id');
        var idPrefix = id.replace('linkType', '');
        var elements = ['viewReference', 'url', 'route', 'attachedWidget', 'target'];

        for (var i = elements.length - 1; i >= 0; i--) {
            $vic('#' + idPrefix + elements[i]).parents('.' + elements[i] + '-type').addClass('vic-hidden');
        };
        console.log('#' + idPrefix + element);
        $vic('#' + idPrefix + element).parents('.' + element + '-type').removeClass('vic-hidden');
    });
}

//Autorun
$vic(document).on('victoire_modal_open_after', function(){
    showSelectedLinkType($vic("[data-role='vic-linkType-select']"));
});
