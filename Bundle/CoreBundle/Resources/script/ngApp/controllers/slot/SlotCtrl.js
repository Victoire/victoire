ngApp.controller("SlotController", ["$scope",
    function($scope) {
        this.init = function(slotId, options) {
            $scope.slotId = slotId;
            $scope.options = options;
            $scope.toggleEnableButtons();
            $scope.newContentButton = "";


        };
        $scope.rebuildActions = function() {



            //var newContentButton = $('.vic-new-widget', '#vic-slot-' + $scope.slotId).first();
            //newContentButton.addClass('vic-new-widget-disabled');
            //$('.vic-new-widget', '#vic-slot-' + $scope.slotId).remove();
            //$('.vic-widget-container', '#vic-slot-' + $scope.slotId).after(newContentButton);
            //$('#vic-slot-' + $scope.slotId).prepend(newContentButton.clone());
            //$scope.toggleEnableButtons();
        };
        $scope.toggleEnableButtons = function() {
            widgets = $('.vic-widget-container', '#vic-slot-' + $scope.slotId);
            if (!("max" in $scope.options) || ("max" in $scope.options) && $scope.options.max > widgets.length) {
                $('.vic-new-widget', '#vic-slot-' + $scope.slotId).removeClass('vic-new-widget-disabled');
            } else {
                $('.vic-new-widget', '#vic-slot-' + $scope.slotId).addClass('vic-new-widget-disabled');
            }
        };

    }
]);
