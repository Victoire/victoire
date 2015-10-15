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
    /**
     * @param View $view
     * @param $entity
     *
     * @return string
     */
    public function getViewReferenceId(View $view, $entity = null)
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
     * @param \SimpleXMLElement $rootNode
     * @param array             $viewReference
     */
    public function removeViewReference(\SimpleXMLElement $rootNode, array $viewReference)
    {
        //Clean by searching by id
        $regex = sprintf("//viewReference[@id='%s']", $viewReference['id']);

        foreach ($rootNode->xpath($regex) as $item) {
            unset($item[0]);
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     *
     * @return array
     */
    public function convertXmlCacheToArray($xml)
    {
        $cachedArray = json_decode(json_encode((array) $xml), true);
        $viewsReferences = [];

        // if the xml contains only one reference, it'll be flatten so it will miss one deep level, so we re-create it
        if (count($cachedArray['viewReference']) === 1) {
            $cachedArray = array_map(function ($el) {
                    return [$el];
                }, $cachedArray);
        }
        foreach ($cachedArray['viewReference'] as $cachedViewReference) {
            $viewReference['id'] = !empty($cachedViewReference['@attributes']['id']) ? $cachedViewReference['@attributes']['id'] : null;
            $viewReference['locale'] = !empty($cachedViewReference['@attributes']['locale']) ? $cachedViewReference['@attributes']['locale'] : null;
            $viewReference['entityId'] = !empty($cachedViewReference['@attributes']['entityId']) ? $cachedViewReference['@attributes']['entityId'] : null;
            $viewReference['entityNamespace'] = !empty($cachedViewReference['@attributes']['entityNamespace']) ? $cachedViewReference['@attributes']['entityNamespace'] : null;
            $viewReference['url'] = !empty($cachedViewReference['@attributes']['url']) ? $cachedViewReference['@attributes']['url'] : null;
            $viewReference['viewId'] = !empty($cachedViewReference['@attributes']['viewId']) ? $cachedViewReference['@attributes']['viewId'] : null;
            $viewReference['viewNamespace'] = !empty($cachedViewReference['@attributes']['viewNamespace']) ? $cachedViewReference['@attributes']['viewNamespace'] : null;
            $viewReference['patternId'] = !empty($cachedViewReference['@attributes']['patternId']) ? $cachedViewReference['@attributes']['patternId'] : null;
            $viewReference['name'] = !empty($cachedViewReference['@attributes']['name']) ? $cachedViewReference['@attributes']['name'] : null;

            $viewsReferences[] = $viewReference;
        }

        return $viewsReferences;
    }
}
