<?php

namespace Victoire\Bundle\ViewReferenceBundle\Helper;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

/**
 * ref: victoire_view_reference.helper
 */
class ViewReferenceHelper
{
    const properties = ["id", "locale", "entityId", "entityNamespace", "slug", "viewId", "viewNamespace", "patternId", "name"];

    /**
     * @param View  $view
     * @param mixed $entity
     *
     * @return string
     */
    public static function generateViewReferenceId(View $view, $entity = null)
    {
        $id = $view->getId();
        if ($view instanceof BusinessPage) {
            $entity = $view->getBusinessEntity();
            if ($view instanceof VirtualBusinessPage) {
                $id = $view->getTemplate()->getId();
            }
        } elseif (!$view instanceof WebViewInterface) {
            return $view->getId();
        }

        $refId = sprintf('ref_%s', $id);
        if ($entity) {
            $refId .= '_'.$entity->getId();
        }

        return $refId;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public static function buildXpath(array $parameters)
    {
        $arguments = [];
        foreach ($parameters as $key => $value) {
            $arguments[$key] = '@'.$key.'="'.$value.'"';
        }

        return '//viewReference['.implode(' and ', $arguments).']';
    }
}
