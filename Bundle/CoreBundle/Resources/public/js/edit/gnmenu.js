/**
 * gnmenu.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */

$vic(document).on('click', '#vic-menu-leftnavbar-trigger', function(event) {
    event.preventDefault();
    $vic('#vic-menu-leftnavbar-trigger').toggleClass('is-in');
    $vic('.vic-menu-leftnavbar').toggleClass('is-in');
});