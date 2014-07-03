<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Victoire\Bundle\PageBundle\Event\Menu\BasePageMenuContextualEvent;
use Victoire\Bundle\CoreBundle\Menu\MenuBuilder;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;
use AppVentus\Awesome\ShortcutsBundle\Service\ShortcutService;

/**
 * When dispatched, this listener add items to a KnpMenu
 */
class BusinessEntityTemplateMenuListener
{
    protected $menuBuilder = null;
    protected $shortcuts = null;

    /**
     * Constructor
     *
     * @param MenuBuilder     $menuBuilder
     * @param ShortcutService $shortcuts
     */
    public function __construct(MenuBuilder $menuBuilder, ShortcutService $shortcuts)
    {
        $this->menuBuilder = $menuBuilder;
        $this->shortcuts = $shortcuts;
    }

    /**
     * Add a global menu item
     *
     * @param Event $event
     *
     * @return Menu
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function addGlobal(Event $event)
    {
        $this->mainItem = $this->menuBuilder->getTopNavbar();

        $this->mainItem
            ->addChild('menu.business_entity_template', array(
                'route' => 'victoire_businessentitytemplate_businessentity_index'
            ))
            ->setLinkAttribute('data-toggle', 'vic-modal');

        return $this->mainItem;
    }

    /**
     * Add the parent menu for a page that extends another one
     *
     * @param BasePageMenuContextualEvent $event
     * @return MenuBuilder
     */
    public function addContextual(BasePageMenuContextualEvent $event)
    {
        $mainItem = $this->menuBuilder->getTopNavbar();

        //get the current page
        $page = $event->getPage();
        $entity = $event->getEntity();

        //are we in a business entity template with an entity given as parameter
        if (($page->getType() === BusinessEntityTemplatePage::TYPE)  && ($entity !== null)) {
            $template = $page;
        } else {
            //get the parent
            $template = $page->getTemplate();
        }

        //if there is a template, we add the link in the top bar
        if ($template !== null) {
            $shortcuts = $this->shortcuts;

            $url = $shortcuts->generateUrl('victoire_core_page_show', array('url' => $template->getUrl()));

            $mainItem
                ->addChild('menu.parent', array(
                    'uri' => $url //we use the root url
                ));
        }

        return $mainItem;
    }
}
