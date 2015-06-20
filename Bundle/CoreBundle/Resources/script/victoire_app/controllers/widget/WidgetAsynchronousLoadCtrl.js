victoire_app.controller("WidgetAsynchronousLoadController", ["$scope", "widgetService", "$sce",
    function($scope, widgetService, $sce) {
        this.fetchAsynchronousWidget = function(widgetId) {
            html = widgetService.fetchStorage(widgetId);
            if (!html) {

                var url = Routing.generate('victoire_core_widget_show', {'id': widgetId, 'viewReferenceId': viewReferenceId});
                $http.get(url)
                    .success(function (data, status, headers, config) {
                        $scope.html = $sce.trustAsHtml(data.html);
                        if(typeof(Storage) !== "undefined") {
                            localStorage.setItem('victoire__widget__html__' + widgetId, data.html);
                        }
                    })
                    .error(function (data, status, headers, config) {
                        console.error(widgetId + ' widget API "get call" has failed.');
                    });
            } else {
                $scope.html = $sce.trustAsHtml(html);
            }
        };
    }
]);

/* asynchronousWidget service
 ================================================== */
victoire_app.service("widgetService", [
    function() {
        this.fetchStorage = function(widgetId) {
            if(typeof(Storage) !== "undefined") {
                return localStorage.getItem('victoire__widget__html__' + widgetId);
            }
        };
    }
]);
