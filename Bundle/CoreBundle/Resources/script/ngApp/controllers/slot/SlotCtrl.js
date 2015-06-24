ngApp.controller("SlotController", ["$scope", "slotLocalStorageService", "slotAPIService", "$sce",
    function($scope, $slotLocalStorageService, $slotAPI, $sce) {
        this.init = function(slotId, options) {
            $scope.slotId = slotId;
            $scope.options = options;
            $scope.getNewContentActionButton();
        };
        $scope.rebuildActions = function() {
            var newContentButton = angular.element('.vic-new-widget').first();
            angular.element('.vic-new-widget').remove();
            angular.element('.vic-widget-container').after(newContentButton);
            angular.element('.vic-slot').prepend(newContentButton.clone());
        };
        $scope.getNewContentActionButton = function() {
            var html = $slotLocalStorageService.fetchStorage($scope.slotId);
            if (!html) {
                var promise = $slotAPI.newContentButton($scope.slotId, $scope.options);
                promise.then(
                    function(payload) {
                        html = payload.data.html;
                        $slotLocalStorageService.store($scope.slotId, html);
                        $scope.newContentActionButtonHtml = $sce.trustAsHtml(html);
                    },
                    function(errorPayload) {
                        console.error($scope.slotId + ' slot newContentButton API fetch has failed.');
                        console.error(errorPayload);
                    });
            } else {
                $scope.newContentActionButtonHtml = $sce.trustAsHtml(html);
            }
        };
    }
]);