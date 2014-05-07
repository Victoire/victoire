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
    const WIDGET_PRE_RENDER = 'victoire_cms.widget.pre_render';
    /**
     * The WIDGET_RENDER event occurs when a widget is rendered
     */
    const WIDGET_POST_RENDER = 'victoire_cms.widget.post_render';
    /**
     * The WIDGET_RENDER event occurs when a widget is rendered
     */
    const WIDGET_POST_QUERY = 'victoire_cms.widget.post_query';
    /**
     * The WIDGET_RENDER event occurs when a widget is rendered
     */
    const WIDGET_BUILD_FORM = 'victoire_cms.widget.build_form';

}
