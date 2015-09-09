/**
 * This function is use to display or hide link fields.
 * If the hidden input "current" is not null, this function show the good fields
 * This hidden input has been added in the linkType because when the "data-refreshOnChange" was used,
 * link field took 'none link' value, or value shouldn't be change.
 */
function showSelectedLinkType(selects) {
    $vic(selects).each(function(){
        var select    = $vic(this);
        var current   = $vic('.vic-current-link').val();
        (current) ? $vic('.vic-current-link').val('') : current = $vic(this).val();
        var container = select.closest('.vic-link-container');
        var active    = container.find('.' + current + '-type');
        var count     = (current != 'none' && container.find('.analytics-type').length) ? 2 : 1;
        var length    = (active.length + count < 4) ? 12 / (active.length + count) : 6;
        var column    = container.hasClass('-horizontal') ?'col-sm-' + length : 'col-sm-12';

        container.find('.vic-form-group').addClass('vic-hidden');
        for (var i = 0; i <= 12; i++) {
            container.find('.vic-form-group').removeClass('col-sm-' + i);
        }
        select.closest('.vic-form-group').removeClass('vic-hidden').addClass(column);
        active.removeClass('vic-hidden').addClass(column);
        if (count > 1) {
            $('.analytics-type').removeClass('vic-hidden').addClass(column);
        }
    });
}

//Autorun
$vic(document).on('victoire_modal_open_after', function(){
    showSelectedLinkType($vic("[data-role='vic-linkType-select']"));
});
