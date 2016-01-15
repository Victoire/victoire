angular.module('ngApp').service("slotLocalStorageService", [
    function() {
        this.fetchStorage = function(slotId) {
            if(typeof(Storage) !== "undefined" && debug != undefined && debug === false) {
                var object = JSON.parse(localStorage.getItem('victoire__slot__newContent__html__' + slotId));
                if (object != undefined) {
                    storedAt = object.timestamp;
                    now = new Date().getTime().toString();
                    //More than an hour ? forget and remove
                    //if ((now - storedAt)/1000 < 3600) {
                    return object.data;
                    /*} else {
                     localStorage.removeItem('victoire__slot__newContent__html__' + slotId);
                     }*/
                }
            }
        };
        this.store = function(slotId, html) {
            if(typeof(Storage) !== "undefined") {
                var object = {data: html, timestamp: new Date().getTime()};
                localStorage.setItem('victoire__slot__newContent__html__' + slotId, JSON.stringify(object));
            }
        };
    }
]);
