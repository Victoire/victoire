<?php

namespace Victoire\Bundle\TemplateBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\TemplateBundle\Event\Menu\TemplateMenuContextualEvent;

/**
 */

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class TemplateMenuListener implements MenuListenerInterface
{
    private $menuBuilder;

    /**
     * {@inheritdoc}
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add a contextual menu item.
     *
     * @param TemplateMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>
     */
    public function addContextual($event)
    {
        $mainItem = $this->getMainItem();
        $template = $event->getTemplate();

        //this contextual menu appears only for template
        $mainItem->addChild('menu.template.settings',
            [
                'route'           => 'victoire_template_settings',
                'routeParameters' => ['slug' => $template->getSlug()],
                ]
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * add a global menu item.
     *
     * @param Event $event
     *
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>
     */
    public function addGlobal(Event $event)
    {
        $mainItem = $this->getMainItem();
        $mainItem->addChild('menu.template.new', [
            'route' => 'victoire_template_new',
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');

        $mainItem->addChild('menu.template.index', [
            'route' => 'victoire_template_index',
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    public function getMainItem()
    {
        if ($menuTemplate = $this->menuBuilder->getTopNavbar()->getChild('menu.template')) {
            return $menuTemplate;
        } else {
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getTopNavbar(),
                'menu.template'
            );
        }
    }
}
