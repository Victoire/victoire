<?php
namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\CoreBundle\Listener\MenuListenerInterface;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\PageBundle\Event\Menu\PageMenuContextualEvent;

/**
 * When dispatched, this listener add items to a KnpMenu
 */
class BlogMenuListener implements MenuListenerInterface
{
    protected $menuBuilder;

    /**
     * Blog menu listener constructor
     *
     * @param MenuBuilder $menuBuilder
     */
    public function __construct(MenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * add a contextual menu item
     *
     * @param PageMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function addContextual($event)
    {
        $mainItem = $this->getMainItem();
        $currentBlog = $event->getPage()->getBusinessEntity()->getBlog();
        $currentArticle = $event->getPage()->getBusinessEntity();

        $mainItem->addChild('menu.blog.article.settings',
            array(
                'route' => 'victoire_blog_article_settings',
                'routeParameters' => array('id' => $currentArticle->getId())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        $mainItem->addChild('menu.blog.article.settings',
            array(
                'route' => 'victoire_blog_article_settings',
                'routeParameters' => array('id' => $currentArticle->getId())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        $mainItem->addChild('menu.blog.article.new',
            array(
                'route'           => 'victoire_blog_article_newBlogArticle',
                'routeParameters' => array('id' => $currentBlog->getId())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * add a blog contextual menu item
     *
     * @param PageMenuContextualEvent $event
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function addBlogContextual($event)
    {
        $mainItem = $this->getMainItem();

        $mainItem->addChild('menu.blog.settings',
            array(
                'route'           => 'victoire_blog_settings',
                'routeParameters' => array('id' => $event->getPage()->getId())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        $mainItem->addChild('menu.blog.translate',
            array(
                'route'           => 'victoire_blog_translate',
                'routeParameters' => array('id' => $event->getPage()->getId())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        $mainItem->addChild('menu.blog.article.new',
            array(
                'route'           => 'victoire_blog_article_newBlogArticle',
                'routeParameters' => array('id' => $event->getPage()->getId())
                )
        )->setLinkAttribute('data-toggle', 'vic-modal');

        return $mainItem;
    }

    /**
     * add global menu items
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
            $this->menuBuilder->getLeftNavbar()->addChild(
                'menu.blog', array(
                    'route' => 'victoire_blog_index'
                )
            )->setLinkAttribute('data-toggle', 'vic-modal');
        }
    }

    /**
     * This method returns you the main item and create it if not exists
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
                "menu.blog",
                array("attributes" => array("class" => "vic-pull-left vic-text-center"))
            );
        }
    }
}
