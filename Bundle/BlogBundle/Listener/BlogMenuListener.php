<?php

namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;

/**
 * When dispatched, this listener add items to a KnpMenu.
 */
class BlogMenuListener implements MenuListenerInterface
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
        $floatActionDropdown = $this->menuBuilder->getFloatActionDropdown();

        $currentArticle = $event->getPage()->getEntity();
        $currentBlog = $currentArticle->getBlog();

        $floatActionDropdown->addChild('menu.blog.article.new',
            [
                'route'           => 'victoire_blog_article_new',
                'routeParameters' => ['id' => $currentBlog->getId()],
                'linkAttributes'  => [
                    'class' => 'v-drop__anchor',
                ],
            ]
        )->setLinkAttribute('data-toggle', 'vic-modal');
    }

    /**
     * add a blog contextual menu item.
     *
     * @param PageMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function addBlogContextual($event)
    {
        $floatActionDropdown = $this->menuBuilder->getFloatActionDropdown();

        $floatActionDropdown->addChild('menu.blog.article.new',
            [
                'route'           => 'victoire_blog_article_new',
                'routeParameters' => ['id' => $event->getPage()->getId()],
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
        if ($this->menuBuilder->isGranted('ROLE_VICTOIRE_BLOG')) {
            $this->menuBuilder->getTopNavbar()->addChild(
                'menu.leftnavbar.blog.label', [
                    'route'      => 'victoire_blog_index',
                ]
            )->setLinkAttribute('data-toggle', 'vic-modal');
        }
    }

    /**
     * This method returns you the main item and create it if not exists.
     *
     * @return \Knp\Menu\ItemInterface The main item to get
     */
    private function getMainItem()
    {
        //if not exists, create it and return it
        if ($menuPage = $this->menuBuilder->getTopNavbar()->getChild(('menu.blog'))) {
            return $menuPage;
        } else {
            //else, find it and return it
            return $this->menuBuilder->createDropdownMenuItem(
                $this->menuBuilder->getTopNavbar(),
                'menu.blog',
                ['attributes' => ['class' => 'vic-pull-left vic-text-left']]
            );
        }
    }
}
