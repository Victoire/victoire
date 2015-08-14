if (window.ngDependencies == undefined) {
    window.ngDependencies = [];
}

window.ngDependencies.push("ngSanitize");

var ngApp = angular.module('ngApp', window.ngDependencies).
    config(function($interpolateProvider){$interpolateProvider.startSymbol('{[{').endSymbol('}]}');});
