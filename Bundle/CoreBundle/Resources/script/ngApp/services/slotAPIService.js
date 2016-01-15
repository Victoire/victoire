angular.module('ngApp').service("slotAPIService", ["$http",
    function($http) {
        this.newContentButton = function(slotId, options) {
            var url = Routing.generate('victoire_core_slot_newContentButton', {
                'slotId': slotId,
                'options': JSON.stringify(options)
            });
            return $http.get(url);
        };
    }
]);
