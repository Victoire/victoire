<?php
namespace Victoire\Bundle\CoreBundle\Helper;

use Doctrine\ORM\EntityManager;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_core.widget_helper
 */
class WidgetHelper
{
    protected $em = null;

    /**
     * Constructor
     *
     * @param EntityManager $em The entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Find a widget by its id
     *
     * @param integer $widgetId
     * @return Widget
     */
    public function findOneById($widgetId)
    {
        $em = $this->em;

        $widgetRepo = $em->getRepository('VictoireCoreBundle:Widget');

        $widget = $widgetRepo->findOneById($widgetId);

        return $widget;
    }
}
