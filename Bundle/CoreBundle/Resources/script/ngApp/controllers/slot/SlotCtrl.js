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

ngApp.service("slotLocalStorageService", [
    function() {
        this.fetchStorage = function(slotId) {
            if(typeof(Storage) !== "undefined" && debug != undefined && debug === false) {
                var object = JSON.parse(localStorage.getItem('victoire__slot__newContent__html__' + slotId));
                if (object != undefined) {
                    storedAt = object.timestamp;
                    now = new Date().getTime().toString();
                    //More than an hour ? forget and remove
                    if ((now - storedAt)/1000 < 3600) {
                        return object.data;
                    } else {
                        localStorage.removeItem('victoire__slot__newContent__html__' + slotId);
                    }
                }
            }
        };
        this.store = function(slotId, html) {
            if(typeof(Storage) !== "undefined") {
                var object = {data: html, timestamp: new Date().getTime()};
                localStorage.setItem('victoire__slot__newContent__html__' + slotId, JSON.stringify(object));
            }
        };
    }
]);

ngApp.service("slotAPIService", ["$http",
    function($http) {
        this.newContentButton = function(slotId, options) {
            var url = Routing.generate('victoire_core_slot_newContentButton', {
                'slotId': slotId,
                'options': JSON.stringify(options)
            });
            return $http.get(url);
        };
    }
]);