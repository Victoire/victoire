victoire_app.controller("WidgetAsynchronousLoadController",
    ["$scope", "widgetLocalStorageService", "widgetAPIService", "slotLocalStorageService", "$sce",
    function($scope, $widgetLocalStorageService, $widgetAPI, $slotLocalStorageService, $sce) {
        this.fetchAsynchronousWidget = function(widgetId) {
            html = $widgetLocalStorageService.fetchStorage(widgetId);
            if (!html) {
                var promise = $widgetAPI.show(widgetId);
                promise.then(
                    function(payload) {
                        html = payload.data.html;
                        console.log(html);
                        $widgetLocalStorageService.store(widgetId, html);
                        $scope.html = $sce.trustAsHtml(html);
                    },
                    function(errorPayload) {
                        console.error(widgetId + ' widget API "get call" has failed.');
                        console.error(errorPayload);
                    });
            } else {
                $scope.html = $sce.trustAsHtml(html);
            }
        };
    }
]);

victoire_app.service("widgetLocalStorageService", [
    function() {
        this.fetchStorage = function(widgetId) {
            if(typeof(Storage) !== "undefined") {
                var object = JSON.parse(localStorage.getItem('victoire__widget__html__' + widgetId));
                if (object != undefined) {
                    storedAt = object.timestamp;
                    now = new Date().getTime().toString();
                    //More than a week content has probably changed, forget and remove
                    if ((now - storedAt)/1000 < 604800) {
                        return object.data;
                    } else {
                        localStorage.removeItem('victoire__widget__html__' + widgetId);
                    }
                }
            }
        };
        this.store = function(widgetId, html) {
            if(typeof(Storage) !== "undefined") {
                var object = {data: html, timestamp: new Date().getTime()};
                localStorage.setItem('victoire__widget__html__' + widgetId, JSON.stringify(object));
            }
        };
    }
]);

victoire_app.service("widgetAPIService", ["$http",
    function($http) {
        this.show = function(widgetId) {
            var url = Routing.generate('victoire_core_widget_show', {'id': widgetId, 'viewReferenceId': viewReferenceId});
            return $http.get(url);
        };
    }
]);
