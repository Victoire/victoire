//To avoid conflicts, Victoire brings to you in AdminMode a prefixed version of jQuery
//To use it, please use $vic instead of $ and use it only in admin views (ROLE_VICTOIRE)
$vic = jQuery.noConflict();

//if previously there was no jquery, we keep the victoire jquery version activated for the external librairies used by victoire
if (typeof($) === "undefined") {
    //no jquery was included before we included the jquery of victoire
    $ = $vic;
    
    console.error('Victoire requires jQuery and no instance was found. A jQuery instance has been initialized to allow Victoire to run. This jQuery instance is only available when you are editing a page. If you need jQuery in the page, be sure to include it when you are not logged in as a Victoire user.');
}
