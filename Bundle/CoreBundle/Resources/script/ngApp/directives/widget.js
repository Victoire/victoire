angular.module('ngApp').directive('widget', function ($compile, $rootScope) {
    return {
        restrict:'A',
        scope: {
            widgetMap: '='
        },
        link: function(scope, element, attrs) {
            scope.positions = [];
            var renderButton = function () {
                if (element.prev().attr('position') && element.prev().attr('widget-map') == element.attr('widget-map')) {
                    element.prev().remove();
                }
                if (element.next().attr('position') && element.next().attr('widget-map') == element.attr('widget-map')) {
                    element.next().remove();
                }

                widgets = $vic('.vic-widget-container', '#vic-slot-' + scope.$parent.slotId);
                if (!("max" in scope.$parent.options) || ("max" in scope.$parent.options) && scope.$parent.options.max > widgets.length) {
                    if (scope.positions.before == 1) {
                        var buttonBefore = angular.element('<new-widget-button position="before" widget-map="' + scope.widgetMap + '"></new-widget-button>');
                        var templateBefore = $compile(buttonBefore);
                        element.before(buttonBefore);
                        templateBefore(scope);
                    }
                    if (scope.positions.after == 1) {
                        var buttonAfter = angular.element('<new-widget-button position="after" widget-map="' + scope.widgetMap + '"></new-widget-button>');
                        var templateAfter = $compile(buttonAfter);
                        element.after(buttonAfter);
                        templateAfter(scope);
                    }
                }
            };

            $rootScope.$watch('widgetMaps', function(widgetMaps) {
                var slot = scope.$parent.slotId;
                var widgetMap = scope.widgetMap;

                var positions = [];

                if (widgetMaps && widgetMaps.hasOwnProperty(slot)) {
                    if (widgetMaps[slot][widgetMap]) {
                        positions = widgetMaps[slot][widgetMap];
                    } else {
                        for (var key in widgetMaps[slot]) {
                            if (widgetMaps[slot].hasOwnProperty(key)) {
                                if (widgetMaps[slot][key].replaced == widgetMap) {
                                    positions = widgetMaps[slot][key];
                                }
                            }
                        }
                    }

                    if (positions) {
                        if (positions.replaced) {
                            angular.element(element).attr('widget-map', positions.id);
                            scope.widgetMap = positions.id;
                        }
                        if (positions) {
                            scope.positions = positions;
                        }

                        renderButton();
                    }
                }
            });
        }
    };
});

