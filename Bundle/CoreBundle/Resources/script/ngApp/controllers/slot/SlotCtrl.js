angular.module('ngApp').controller("SlotController", ["$scope",
    function($scope) {
        this.init = function(slotId, options) {
            $scope.slotId = slotId;
            $scope.options = options;
            $scope.toggleEnableButtons();
            $scope.newContentButton = "";


        };
        $scope.toggleEnableButtons = function() {
            }
        };

    }
]);



