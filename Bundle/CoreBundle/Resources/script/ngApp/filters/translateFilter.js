angular.module('ngApp').filter('translate', function() {
    return function(input) {
        return Translator.trans(input, {}, 'victoire');
    };
});
