angular.module('ngApp').service("widgetLocalStorageService", [
    function() {
        this.fetchStorage = function(widgetId) {
            if(typeof(Storage) !== "undefined") {
                var object = JSON.parse(localStorage.getItem('victoire__widget__html__' + widgetId));
                if (object != undefined) {
                    storedAt = object.timestamp;
                    now = new Date().getTime().toString();

                    //More than a week content has probably changed, forget and remove
                    if ((now - storedAt)/1000 < 604800 || !navigator.onLine) {
                        return object.data;
                    } else {
                        localStorage.removeItem('victoire__widget__html__' + widgetId);
                    }
                }
            }
        };
        this.store = function(widgetId, html) {
            if (typeof(Storage) !== "undefined") {
                var object = {data: html, timestamp: new Date().getTime()};
                localStorage.setItem('victoire__widget__html__' + widgetId, JSON.stringify(object));
            }
        };
    }
]);
