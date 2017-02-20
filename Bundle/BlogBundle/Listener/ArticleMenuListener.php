<?php

namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class ArticleMenuListener implements MenuListenerInterface
{
    protected $menuBuilder;

    /**
     * Blog menu listener constructor.
     *
     * @param MenuBuilder $menuBuilder
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add a contextual menu item.
     *
     * @param PageMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function addContextual($event)
    {
        $page = $event->getPage();
        $currentArticle = $event->getPage()->getBusinessEntity();

        $bottomRightNavbar = $this->menuBuilder->getBottomRightNavbar();

        $bottomRightNavbar->addChild('menu.page.settings',
            [
                'route'           => 'victoire_blog_article_settings',
                'routeParameters' => [
                    'id'      => $currentArticle->getId(),
                    'page_id' => $page->getId(),
                ],
                'linkAttributes'  => [
                    'class' => 'v-btn v-btn--sm v-btn--transparent v-test',
                    'id' => 'v-settings-link',
                ],
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');
    }

    /**
     * add global menu items.
     *
     * @param Event $event
     *
     * @return \Victoire\Bundle\BlogBundle\Listener\MenuItem
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function addGlobal(Event $event)
    {
    }

    /**
     * This method returns you the main item and create it if not exists.
     *
     * @return \Knp\Menu\ItemInterface The main item to get
     */
    private function getMainItem()
    {
        $menuPage = $this->menuBuilder->getTopNavbar()->getChild('menu.page');

        if ($menuPage) {
            return $menuPage;
        } else {
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getTopNavbar(),
                'menu.page'
            );
        }
    }

}
