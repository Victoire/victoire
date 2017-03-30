angular.module('ngApp').directive('newWidgetButton', function ($compile) {
    return {
        restrict:'E',
        link: function(scope, element, attrs) {
            var availableWidgets = angular.element($vic(element).parents('.vic-slot').first()).scope().options.availableWidgets;
            var choices = [{value: '', label: ''}];

            for (var name in availableWidgets) {
                choices.push({
                    value: name,
                    label: 'widget.' + name.toLowerCase() + '.new.action.label'
                });
            }

            // Eval width of the slots to add or not a class
            // function declared into the widget.js script file
            evalSlotWidth(element[0].querySelector('.v-slot'));

            scope.selectedChoices = choices[0];
            scope.choices = choices;

        },
        templateUrl: '/bundles/victoirecore/js/angular/directives/newWidgetButton/newWidgetButton.html'
    };
});
