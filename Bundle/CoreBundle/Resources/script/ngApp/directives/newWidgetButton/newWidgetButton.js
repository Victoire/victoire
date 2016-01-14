ngApp.directive('newWidgetButton', function ($compile) {
    return {
        restrict:'E',
        link: function(scope, element, attrs) {
            //todo: flatten availableWidgets
            scope.availableWidgets = angular.element($vic(element).parents('.vic-slot').first()).scope().options.availableWidgets;
            //scope.availableWidgets = scope.$parent.options.availableWidgets;
            //scope.availableWidgets = scope.$parent.options.availableWidgets;
            //var $parentScope = angular.element($vic(element).parent().parents('.vic-slot').first()).scope();
            //console.log(scope.availableWidgets);
            //scope.availableWidgets = $parentScope.options.availableWidgets;
        },
        templateUrl: '/bundles/victoirecore/js/angular/directives/newWidgetButton/newWidgetButton.html'
    };
});
