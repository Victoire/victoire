<?php
namespace Victoire\Bundle\CoreBundle;

/**
 * Contains all events thrown in the VictoireCoreBundle
 */
final class VictoireCmsEvents
{
    /**
     * The WIDGET_RENDER event occurs when a widget is rendered
     */
    const WIDGET_PRE_RENDER = 'victoire_core.widget.pre_render';
    /**
     * The WIDGET_RENDER event occurs when a widget is rendered
     */
    const WIDGET_POST_RENDER = 'victoire_core.widget.post_render';
    /**
     * The WIDGET_RENDER event occurs when a widget is rendered
     */
    const WIDGET_POST_QUERY = 'victoire_core.widget.post_query';
    /**
     * The WIDGET_RENDER event occurs when a widget is rendered
     */
    const WIDGET_BUILD_FORM = 'victoire_core.widget.build_form';

}
