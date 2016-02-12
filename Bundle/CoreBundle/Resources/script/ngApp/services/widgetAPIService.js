angular.module('ngApp').service("widgetAPIService", ["$http",
    function($http) {
        this.widget = function(widgetId) {
            var url = Routing.generate('victoire_core_widget_show', {
                'id': widgetId,
                'viewReferenceId': viewReferenceId,
                '_locale': locale
            });

            return $http({
                    method: 'GET',
                    url: url,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
        };
        this.widgets = function(widgetIds) {
            var url = Routing.generate('victoire_core_widget_apiWidgets', {
                'widgetIds': JSON.stringify(widgetIds),
                'viewReferenceId': viewReferenceId,
                '_locale': locale
            });

            return $http({
                method: 'GET',
                url: url,
                headers: {
                    'Content-Type': 'application/json'
                }
            });
        };
    }
]);
