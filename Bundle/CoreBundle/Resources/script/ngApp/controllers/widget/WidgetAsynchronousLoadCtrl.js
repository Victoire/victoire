ngApp.controller("WidgetAsynchronousLoadController",
    ["$scope", "widgetLocalStorageService", "widgetAPIService", "slotLocalStorageService", "$sce",
    function($scope, $widgetLocalStorageService, $widgetAPI, $slotLocalStorageService, $sce) {
        this.init = function(widgetId) {
            $scope.widgetId = widgetId;
            $scope.fetchAsynchronousWidget();
        },
        $scope.fetchAsynchronousWidget = function() {
            html = $widgetLocalStorageService.fetchStorage($scope.widgetId);
            if (!html) {
                var promise = $widgetAPI.show($scope.widgetId);
                promise.then(
                    function(payload) {
                        html = payload.data.html;
                        $widgetLocalStorageService.store($scope.widgetId, html);
                        $scope.html = $sce.trustAsHtml(html);
                    },
                    function(errorPayload) {
                        console.error($scope.widgetId + ' widget API "get call" has failed.');
                        console.error(errorPayload);
                    });
            } else {
                $scope.html = $sce.trustAsHtml(html);
            }
        };
    }
]);

ngApp.service("widgetLocalStorageService", [
    function() {
        this.fetchStorage = function(widgetId) {
            if(typeof(Storage) !== "undefined" && debug != undefined && debug === false) {
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

ngApp.service("widgetAPIService", ["$http",
    function($http) {
        this.show = function(widgetId) {
            var url = Routing.generate('victoire_core_widget_show', {'id': widgetId, 'viewReferenceId': viewReferenceId});
            return $http.get(url);
        };
    }
]);
