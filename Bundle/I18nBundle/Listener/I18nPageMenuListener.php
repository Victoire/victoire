<?php

namespace Victoire\Bundle\I18nBundle\Listener;

use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Listener\PageMenuListener;

class I18nPageMenuListener extends PageMenuListener
{
    /**
     * Constructor.
     *
     * @param MenuBuilder $menuBuilder
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        parent::__construct($menuBuilder);
    }

    /**
     * This method is call to replace the base contextual PageMenuListener to add a new item in the menu when I18n is activated.
     *
     * @param PageMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface <\Knp\Menu\ItemInterface, NULL>
     */
    public function addContextual($event)
    {
        //get the current page
        $page = $event->getPage();

        $mainItem = $this->getMainItem();

        $mainItem->addChild('menu.page.i18n.addTranslation',
            [
                'route'           => 'victoire_core_page_translate',
                'routeParameters' => ['id' => $page->getId()],
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }
}
