angular.module('ngApp').directive('widget', function ($compile, $rootScope) {
    return {
        restrict:'E',
        scope: {
            widgetMap: '='
        },
        link: function(scope, element, attrs) {
            scope.positions = [];
            var renderButton = function () {
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
            };

            $rootScope.$watch('widgetMaps', function(widgetMaps) {
                var slot = scope.$parent.slotId;
                var widgetMap = scope.widgetMap;

                var positions = [];

                if (widgetMaps) {
                    if (widgetMaps[slot][widgetMap]) {
                        positions = widgetMaps[slot][widgetMap];
                    } else {
                        console.log("slot not found");
                        console.log(widgetMaps[slot]);
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
