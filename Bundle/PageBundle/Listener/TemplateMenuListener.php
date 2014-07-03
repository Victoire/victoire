<?php
namespace Victoire\Bundle\PageBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\BasePageMenuContextualEvent;
use Victoire\Bundle\PageBundle\Entity\Template;

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
        $page = $event->getPage();

        $mainItem = $this->getMainItem();

        //this contextual menu appears only for template
        if ($page instanceof Template) {

            $mainItem->addChild('menu.template.settings',
                array(
                    'route' => 'victoire_core_template_settings',
                    'routeParameters' => array('slug' => $page->getSlug())
                    )
            )->setLinkAttribute('data-toggle', 'vic-modal');
        }

        return $mainItem;
    }

    /**
     * add a global menu item
     */
    public function addGlobal(Event $event)
    {
        $mainItem = $this->getMainItem();
        $mainItem->addChild('menu.template.new', array(
            'route' => 'victoire_core_template_new'
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        $mainItem->addChild('menu.template.index', array(
            'route' => 'victoire_core_template_index'
            )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    private function getMainItem()
    {
        if ($menuTemplate = $this->menuBuilder->getTopNavbar()->getChild('menu.template')) {
            return $menuTemplate;
        } else {
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getTopNavbar(),
                "menu.template"
            );
        }
    }
}
