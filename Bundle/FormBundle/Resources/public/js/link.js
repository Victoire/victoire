function showSelectedLinkType(selects) {
    $vic(selects).each(function(select) {
        var element = $vic(this).val();
        var id = $vic(this).attr('id');
        var idPrefix = id.replace('linkType', '');
        var elements = ['page', 'url', 'route', 'attachedWidget', 'target'];

        for (var i = elements.length - 1; i >= 0; i--) {
            $vic('#' + idPrefix + elements[i]).parents('.' + elements[i] + '-type').addClass('vic-hidden');
        };
        $vic('#' + idPrefix + element).parents('.' + element + '-type').removeClass('vic-hidden');
    });
}

