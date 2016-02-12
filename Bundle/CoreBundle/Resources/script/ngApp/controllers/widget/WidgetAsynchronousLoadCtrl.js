angular.module('ngApp').controller("WidgetAsynchronousLoadController",
    ["$scope", "widgetLocalStorageService", "widgetAPIService", "slotLocalStorageService", "$sce",
    function($scope, $widgetLocalStorageService, $widgetAPI, $slotLocalStorageService, $sce) {
        $scope.init = function(widgetId) {
            $scope.widgetId = widgetId;
        };
        $scope.fetchAsynchronousWidget = function() {

            var promise = $widgetAPI.widget($scope.widgetId);
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
        };
    }
]);

