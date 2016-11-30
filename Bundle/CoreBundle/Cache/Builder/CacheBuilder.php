<?php

namespace Victoire\Bundle\CoreBundle\Cache\Builder;

use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Cache\VictoireCache;

/**
 * Build victoire data entities
 * ref: victoire_core.cache_builder.
 */
class CacheBuilder
{
    private $cache;

    public function __construct(VictoireCache $cache)
    {
        $this->cache = $cache;
    }


    /**
     * save Widget.
     */
    public function saveWidgetReceiverProperties($widgetName, $receiverProperties)
    {
        $widgets = $this->cache->get('victoire_business_entity_widgets', []);
        if (!array_key_exists($widgetName, $widgets)) {
            $widgets[$widgetName] = [];
        }

        $widgets[$widgetName]['receiverProperties'] = $receiverProperties;
        $this->cache->save('victoire_business_entity_widgets', $widgets);
    }

}
