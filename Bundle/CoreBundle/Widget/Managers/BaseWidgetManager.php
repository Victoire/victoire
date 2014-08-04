<?php
namespace Victoire\Bundle\CoreBundle\Widget\Managers;

abstract class BaseWidgetManager
{
    public function __construct()
    {
        error_log(
            sprintf("Warning: Deprecated WidgetManagers does no longer exists.
                Please convert the manager %s to a WidgetContentResolver.
                You have to move the manager from Victoire\Manager\xxx to
                Victoire\Resolver\xxx and tag this service with
                'tags:
                - { name: victoire_widget.widget_content_resolver, alias: Xxx (the widget name) }'."
            , get_class($this)));
    }
}
