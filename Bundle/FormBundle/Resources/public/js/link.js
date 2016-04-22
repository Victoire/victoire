/**
* This function is used to display or hide link fields.
*/
function showSelectedLinkType(selects) {
    $vic(selects).each(function(){
        var select    = $vic(this);
        var current   = $vic(this).val();
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
