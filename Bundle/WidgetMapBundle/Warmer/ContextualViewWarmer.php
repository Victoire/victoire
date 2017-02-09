<?php

namespace Victoire\Bundle\WidgetMapBundle\Warmer;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\ViewReferenceBundle\Connector\ViewReferenceRepository;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;
use Victoire\Bundle\WidgetBundle\Entity\Traits\LinkTrait;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Helper\WidgetHelper;
use Victoire\Bundle\WidgetBundle\Repository\WidgetRepository;
use Victoire\Bundle\WidgetMapBundle\Entity\WidgetMap;

/**
 * ContextualViewWarmer.
 *
 * Ref: victoire_widget_map.contextual_view_warmer
 */
class ContextualViewWarmer
{
    /**
     * Give a contextual View to each WidgetMap used in a View and its Templates.
     * 
     * @param View $view
     *
     * @return WidgetMap[]
     */
    public function warm(View $view)
    {
        $widgetMaps = [];

        foreach ($view->getWidgetMaps() as $_widgetMap) {
            $_widgetMap->setContextualView($view);
            $widgetMaps[] = $_widgetMap;
        }

        if ($template = $view->getTemplate()) {
            $templateWidgetMaps = $this->warm($template);
            $widgetMaps = array_merge($widgetMaps, $templateWidgetMaps);
        }

        return $widgetMaps;
    }
}
