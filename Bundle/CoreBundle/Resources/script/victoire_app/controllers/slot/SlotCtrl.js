victoire_app.controller("SlotController", ["$scope", "slotLocalStorageService", "slotAPIService", "$sce",
    function($scope, $slotLocalStorageService, $slotAPI, $sce) {
        this.addActions = function(slotId, options) {
            $scope.getNewContentActionButton(slotId, options);
        };
        $scope.getNewContentActionButton = function(slotId, options) {
            var html = $slotLocalStorageService.fetchStorage(slotId);
            if (!html) {
                var promise = $slotAPI.newContentButton(slotId, options);
                promise.then(
                    function(payload) {
                        html = payload.data.html;
                        $slotLocalStorageService.store(slotId, html);
                        $scope.newContentActionButtonHtml = $sce.trustAsHtml(html);
                    },
                    function(errorPayload) {
                        console.error(slotId + ' slot newContentButton API fetch has failed.');
                        console.error(errorPayload);
                    });
            } else {
                $scope.newContentActionButtonHtml = $sce.trustAsHtml(html);
            }
        };
    }
]);

victoire_app.service("slotLocalStorageService", [
    function() {
        this.fetchStorage = function(slotId) {
            if(typeof(Storage) !== "undefined") {
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

victoire_app.service("slotAPIService", ["$http",
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