<?php

namespace Victoire\Bundle\ViewReferenceBundle;


final class ViewReferenceEvents
{

    /**
     * The UPDATE_VIEW_REFERENCE event occurs when a redis reference need to be updated.
     */
    const UPDATE_VIEW_REFERENCE = 'victoire.view_reference.update';

    /**
     * The REMOVE_VIEW_REFERENCE event occurs when the redis reference need to be deleted.
     */
    const REMOVE_VIEW_REFERENCE = 'victoire.view_reference.remove';
}