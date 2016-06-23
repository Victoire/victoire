angular.module('ngApp').controller("SlotController", ["$scope", "$compile",
    function($scope, $compile) {
        this.init = function(slotId, options) {
            $scope.slotId = slotId;
            $scope.options = options;
            $scope.toggleEnableButtons();
        };
        $scope.toggleEnableButtons = function() {
            widgets = $vic('.vic-widget-container', '#vic-slot-' + $scope.slotId);
            if (widgets.length == 0) {
                var buttonBefore = angular.element('<new-widget-button title="' + $scope.slotId + '" position="" widget-map=""></new-widget-button>');
                var templateBefore = $compile(buttonBefore);
                $vic('#vic-slot-' + $scope.slotId).append(buttonBefore);
                templateBefore($scope);
            }
        };

    }
]);
