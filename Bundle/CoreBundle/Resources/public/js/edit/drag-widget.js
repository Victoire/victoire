function enableSortableSlots(){
    $vic(".vic-slot").each(function(){
        $vic(this).sortable({
            revert: true,
            connectWith: '.vic-slot',
            handle: '.vic-hover-widget',
            items: ".vic-col-md-12:not(.undraggable)",
            placeholder: "ui-state-highlight",
            forcePlaceholderSize: true,
            revert: true,
            stop: function( event, ui ) {
                // var ajaxCall = updatePosition(ui);
                // var fail = false;
                // ajaxCall.fail(function(){
                //     $vic(".vic-slot").each(function(){
                //         $vic(this).sortable('cancel');
                //     });
                // });

                // ajaxCall.done(function() {
                // });

                replaceDropdown();


            }
        });
        $vic(this).disableSelection();

    });
}

function updatePosition(ui){
    var sorted = {};
    $vic(".vic-slot").each(function(key, el){
        sorted[$vic(el).attr('id')] = $vic(el).sortable('toArray');
    });

    return $vic.post(Routing.generate('victoire_cms_widget_update_position', {'page': pageId}),
        { 'sorted': sorted }
    );
}


function replaceDropdown() {
    $vic('.vic-undraggable').each(function() {
        if ($vic(this).next().hasClass('vic-undraggable')) {
            $vic(this).remove();
        };
    });

    $vic('.vic-slot').each(function() {
        var me = $vic(this);

        if (!(me.children(':first').hasClass('vic-undraggable'))) {
            $vic.ajax({
                type: "GET",
                url: "index.php?page[]=element&page[]=dropdownWidget"
            }).done(function(response){
                me.prepend('<div class="vic-col-md-12 vic-undraggable">'+response+'</div>');
            });
        };
    });

    $vic('.vic-widget').each(function(index, el) {
        var me = $vic(this);
        var slot = me.parent();
        if ( !(me.next()) || !(me.next().hasClass('vic-undraggable'))) {
            $vic.ajax({
                type: "GET",
                url: "index.php?page[]=element&page[]=dropdownWidget"
            }).done(function(response){
                me.after('<div class="vic-col-md-12 vic-undraggable">'+response+'</div>');
            });
        };
    });
};
