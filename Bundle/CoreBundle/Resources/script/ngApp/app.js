if (window.ngDependencies == undefined) {
    window.ngDependencies = [];
}

window.ngDependencies.push("ngSanitize");

var ngApp = angular.module('ngApp', window.ngDependencies).
    config(function($interpolateProvider){$interpolateProvider.startSymbol('{[{').endSymbol('}]}');}).
    config(['$httpProvider', function($httpProvider) {
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    }]);
