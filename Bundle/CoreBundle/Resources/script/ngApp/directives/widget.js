angular.module('ngApp').directive('widget', function ($compile, $rootScope) {
    return {
        restrict:'A',
        scope: {
            widgetMap: '='
        },
        link: function(scope, element, attrs) {
            scope.positions = [];


            // When a change is done in the rootScope's widgetMaps
            $rootScope.$watch('widgetMaps', function(widgetMaps) {
                var slot = scope.$parent.slotId;
                var widgetMap = scope.widgetMap;

                var positions = [];

                if (widgetMaps && widgetMaps.hasOwnProperty(slot)) {
                    // find positions for the current widgetmap or replaced
                    if (widgetMaps[slot][widgetMap]) {
                        positions = widgetMaps[slot][widgetMap];
                    } else {
                        for (var key in widgetMaps[slot]) {
                            if (widgetMaps[slot].hasOwnProperty(key) && widgetMaps[slot][key].replaced == widgetMap) {
                                positions = widgetMaps[slot][key];
                            }
                        }
                    }
                    if (positions) {
                        // If replaced, change current widgetMap to the replaced
                        if (positions.replaced) {
                            angular.element(element).attr('widget-map', positions.id);
                            scope.widgetMap = positions.id;
                        }
                        if (positions) {
                            scope.positions = positions;
                        }

                        // Remove the newWidgetButton up an down myself
                        if (element.prev().attr('position') && element.prev().attr('widget-map') == element.attr('widget-map')) {
                            element.prev().remove();
                        }
                        if (element.next().attr('position') && element.next().attr('widget-map') == element.attr('widget-map')) {
                            element.next().remove();
                        }

                        widgets = $vic('.vic-widget-container', '#vic-slot-' + scope.$parent.slotId);
                        // If max widgets in the slot not reached
                        if (!("max" in scope.$parent.options) || ("max" in scope.$parent.options) && scope.$parent.options.max > widgets.length) {
                            for (var position in scope.positions) {
                                // If position is available
                                if (scope.positions.hasOwnProperty(position) && ['before', 'after'].indexOf(position) != -1 && scope.positions[position] == true) {
                                    var button = angular.element('<new-widget-button  title="' + scope.$parent.slotId + '" position="' + position + '" widget-map="' + scope.widgetMap + '"></new-widget-button>');
                                    var template = $compile(button);
                                    // call "before|after" on element to append or prepend button
                                    element[position](button);
                                    template(scope);
                                }
                            }
                        }
                    }
                }
            });
        }
    };
});
