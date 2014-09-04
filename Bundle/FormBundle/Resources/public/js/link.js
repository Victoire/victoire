function showSelectedLinkType(select) {
    var element = $vic(select).val();
    var id = $vic(select).attr('id');
    var idPrefix = id.replace('linkType', '');
    var elements = ['page', 'url', 'route', 'attachedWidget'];

    for (var i = elements.length - 1; i >= 0; i--) {
        $vic('#' + idPrefix + elements[i]).parents('.' + elements[i] + '-type').addClass('vic-hidden');
    };
    $vic('#' + idPrefix + element).parents('.' + element + '-type').removeClass('vic-hidden');
}

