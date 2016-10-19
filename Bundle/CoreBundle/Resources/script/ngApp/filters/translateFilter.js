angular.module('ngApp').filter('translate', function() {
    return function(input) {
        var originalLocale = Translator.locale;
        Translator.locale = adminLocale;
        var trans = Translator.trans(input, {}, 'victoire');
        Translator.locale = originalLocale;

        return trans;
    };
});
