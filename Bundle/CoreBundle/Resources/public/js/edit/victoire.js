
$vic(document).ready(function() {
    loading(false);
    enableSortableSlots();

    //Display all buttons except the disabled after they have been disabled (by updateSlotActions functions)
    setTimeout(function() {
        $vic.each($vic('.vic-new-widget'), function() {
            if (!$vic(this).hasClass("vic-new-widget-disabled")) {
                $vic(this).removeClass('vic-hidden');
            }
        }) ;
    }, 10);
});

//Used to know when user is leaving or page is refreshing
//->when a victoire user is editing or creating a widget and he refresh the page by error, he press ESC. This class allows victoire to know which action to do
$vic(window).on('beforeunload',function() {
    $vic('body').addClass('page-unloading');
});

$vic(document).on("dblclick", function (e){
    $vic('.vic-ns-show').remove();
    $vic('#vic-switcher-editMode').click();

    if ($vic('#vic-switcher-editMode').is(':checked')) {

        //Are we targeting a widget ? If so find it to click on the link to edit it and auto scroll.
        if ($vic(e.target).hasClass('.vic-widget')) {
            var widget = $vic(e.target);
        } else if ($vic(e.target).hasClass('.vic-widget-container')) {
            var widget = $vic('> .vic-widget', e.target);
        } else if ($vic(e.target).parents('.vic-widget:first') != undefined) {
            var widget = $vic(e.target).parents('.vic-widget').first(); //Because it could be an embed widget
        }

        if (widget != undefined) {
            //Automatic open the widget modal
            $vic('> a.vic-hover-widget', widget).click();
            //Automatic scroll to the widget
            slideTo('#' + $vic('> .vic-anchor', $vic(widget).parent()).attr('id'), 0);
        }

        congrat('Hey Victoire');
    }
});

// Functions
////////////

function trackChange(elem)
{
    if ($vic(elem).val() === 'url') {
        $vic(elem).parent('li').children('.url-type').show();
        $vic(elem).parent('li').children('.page-type').hide();
        $vic(elem).parent('li').children('.route-type').hide();
    } else if ($vic(elem).val() === 'page') {
        $vic(elem).parent('li').children('.page-type').show();
        $vic(elem).parent('li').children('.url-type').hide();
        $vic(elem).parent('li').children('.route-type').hide();
    } else if ($vic(elem).val() === 'route') {
        $vic(elem).parent('li').children('.page-type').hide();
        $vic(elem).parent('li').children('.url-type').hide();
        $vic(elem).parent('li').children('.route-type').show();
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
    var count = $vic('> .vic-widget-container', "#vic-slot-"+slot).size();
    if ( max == undefined || count < max ) {
        $vic("> .vic-new-widget, > .vic-widget-container > .vic-new-widget", "#vic-slot-"+slot).removeClass('vic-new-widget-disabled');
    } else {
        $vic("> .vic-new-widget, > .vic-widget-container > .vic-new-widget", "#vic-slot-"+slot).addClass('vic-new-widget-disabled');
    }
}

function enableSortableSlots(){
    $vic(".vic-slot").each(function(){
        $vic(this).sortable({
            revert: true,
            handle: '.vic-hover-widget',
            items: "> .vic-widget-container:not(.vic-undraggable)",
            placeholder: "vic-ui-state-highlight",

            forcePlaceholderSize: true,
            revert: true,
            stop: function( event, ui ) {
                var ajaxCall = updatePosition(ui);

                //update the positions of the widgets
                updateWidgetPositions();
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

function updateWidgetPositions(slotId){
    if (slotId == undefined || slotId == "") {
        $vic(".vic-slot").each(function() {
            updateWidgetPositions($vic(this).data('name'));
        });
    }
    var position = 1;
    $vic(".vic-slot[data-name='" + slotId + "'] > .vic-widget-container").each(function() {
        $vic(this).attr('data-position', position);
        position = parseInt(position + 1);
    });
}

function updatePosition(ui){
    var sorted = {};
    $vic(".vic-slot").each(function(key, el){
        sorted[$vic(el).data('name')] = $vic(el).sortable('toArray', { attribute: 'data-id' });
    });

    return $vic.post(Routing.generate('victoire_core_widget_update_position', {'viewReference': viewReferenceId}),
        { 'sorted': sorted }
    );
}

function replaceDropdown(ui) {
    $vic(ui.item).children('.vic-dropdown').remove();
    $vic(ui.item).append($vic(ui.item).parents('.vic-slot').children('.vic-dropdown').clone());
}

function loading(value) {
    if (value == undefined) { //Switch mode
        $vic('.vic-topNavbar-logo').toggleClass('vic-loading');
    } else if (value === true) { //Run
        $vic('.vic-topNavbar-logo').addClass('vic-loading');
    } else if (value === false) { //Stop
        $vic('.vic-topNavbar-logo').removeClass('vic-loading');
    }
}

function slideTo(element, duration, effect) {
    if (duration == undefined) { var duration = 1500; }
    if (effect == undefined) { var effect = 'easeInSine'; }

    //get the top offset of the target anchor
    var target_offset = $vic(element).offset();
    var target_top = target_offset.top;

    //goto that anchor by setting the body scroll top to anchor top
    $vic('html, body').animate({scrollTop:target_top}, duration, effect);
}
