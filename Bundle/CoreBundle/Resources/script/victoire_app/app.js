var victoire_app = angular.module('victoire_app', ["ngSanitize"]).
    config(function($interpolateProvider){$interpolateProvider.startSymbol('{[{').endSymbol('}]}');}).
    config(['$httpProvider', function($httpProvider) {
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    }]);
