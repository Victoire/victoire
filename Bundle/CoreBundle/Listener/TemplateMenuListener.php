<?php
namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Event\Menu\BasePageMenuContextualEvent;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;

/**
 */

/**
 * When dispatched, this listener add items to a KnpMenu
 */
class TemplateMenuListener
{
    private $menuBuilder;


    /**
     * {@inheritDoc}
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add a contextual menu item
     */
    public function addContextual(BasePageMenuContextualEvent $event)
    {
        $mainItem = $this->getMainItem();
        $mainItem->addChild('menu.template.settings',
            array(
                'route' => 'victoire_cms_template_settings',
                'routeParameters' => array('slug' => $event->getPage()->getSlug())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * add a global menu item
     */
    public function addGlobal(Event $event)
    {
        $mainItem = $this->getMainItem();
        $mainItem->addChild('menu.template.new', array(
            'route' => 'victoire_cms_template_new'
            )
        );
        $mainItem['menu.template.new']->setLinkAttribute('data-toggle', 'modal');

        $mainItem->addChild('menu.template.index', array(
            'route' => 'victoire_cms_template_index'
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    private function getMainItem()
    {
        if ($menuTemplate = $this->menuBuilder->getMenu()->getChild(('menu.template'))) {
            return $menuTemplate;
        } else {
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getMenu(),
                "menu.template"
            );
        }
    }
}
