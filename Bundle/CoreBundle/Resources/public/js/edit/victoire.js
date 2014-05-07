
$vic(document).ready(function() {

    enableSortableSlots();

    $vic(document).on('change', 'select.theme-choices', function(e) {
        entity = $vic(this).parents('div.tab-pane').attr('id');
        item = $vic('div#' + entity + ' select.theme-choices option:selected').val();
        slot = $vic(this).parents('.slot').attr('id');

        $vic.ajax({
            url: Routing.generate('victoire_core_widget_new', {'type': item, 'page': pageId, 'slot': slot, 'entity': entity}),
            data: item,
            context: document.body,
            type: "POST",
            success: function(data) {
                $vic('div#' + entity + ' select.theme-choices').parents('form').parent().parent().html(data);
            },
            error: function(data) {
                alert("Il semble s'êre produit une erreur");
            }
        });
    });

    //creates the left navbar
    if (typeof(gnMenu) != 'undefined' && document.getElementById('vic-admin-menu') !== null) {
        new gnMenu(document.getElementById('vic-admin-menu'));
    }
});


// Functions
////////////

function trackChange(elem)
{
    if ($vic(elem).val() === 'url') {
        $vic(elem).parent('li').children('.url-type').show();
        $vic(elem).parent('li').children('.page-type').hide();
    } else {
        $vic(elem).parent('li').children('.page-type').show();
        $vic(elem).parent('li').children('.url-type').hide();
    }
}


/**
 * This function is used to update the slot actions (Add some widget )
 * @param  {integer} slot the id of the slot
 * @param  {integer} max  the max number of items
 *
 * @return {void}
 */
function updateSlotActions(slot, max)
{
    var count = $vic('.widget-container', "#vic-slot-"+slot).size();
    if ( max == undefined || count < max ) {
        $vic(".vic-new-widget", "#vic-slot-"+slot).removeClass('vic-new-widget-disabled');
    } else {
        $vic(".vic-new-widget", "#vic-slot-"+slot).addClass('vic-new-widget-disabled');
    }
}

function enableSortableSlots(){
    $vic(".vic-slot").each(function(){
        $vic(this).sortable({
            revert: true,
            connectWith: '.vic-slot',
            handle: '.vic-hover-widget',
            items: ".widget-container:not(.undraggable)",
            placeholder: "vic-ui-state-highlight",

            forcePlaceholderSize: true,
            revert: true,
            stop: function( event, ui ) {
                var ajaxCall = updatePosition(ui);
                var fail = false;
                ajaxCall.fail(function(){
                    $vic(".vic-slot").each(function(){
                        $vic(this).sortable('cancel');
                    });
                });

                replaceDropdown(ui);

            }

        });
    });
}
function updatePosition(ui){
    var sorted = {};
    $vic(".vic-slot").each(function(key, el){
        sorted[$vic(el).attr('id')] = $vic(el).sortable('toArray');
    });

    return $vic.post(Routing.generate('victoire_core_widget_update_position', {'page': pageId}),
        { 'sorted': sorted }
    );
}

function replaceDropdown(ui) {
    $(ui.item).children('.vic-undraggable').remove();
    $(ui.item).append($(ui.item).parents('.vic-slot').children('.vic-dropdown').clone());
}

