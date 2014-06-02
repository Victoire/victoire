<?php
namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Victoire\Bundle\BusinessEntityTemplateBundle\Listener\BusinessEntityTemplateMenuListener;

/**
 * This class add items in admin menu
 *
 * @package Victoire/Menu
 * @author Leny Bernard
 * @author Paul Andrieux
 **/
class MenuListener
{

    protected $eventDispatcher;
    protected $pageMenu;
    protected $templateMenu;
    protected $sitemapMenu;
    protected $businessEntityTemplateMenuListener;

    /**
     * Construct function to include eventDispatcher, pageMenu, TemplateMenu, SiteMapMenu
     *
     * @return void
     * TODO We should tag menu listeners to dynamically get them iof pass them as arg
     **/
    public function __construct($eventDispatcher, $pageMenu, $templateMenu, $sitemapMenu, BusinessEntityTemplateMenuListener $businessEntityTemplateMenuListener)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->pageMenu = $pageMenu;
        $this->templateMenu = $templateMenu;
        $this->sitemapMenu = $sitemapMenu;
        $this->businessEntityTemplateMenuListener = $businessEntityTemplateMenuListener;
    }

    /**
     * Send the event to the global and contextual menus of victoire
     *
     * @param GetResponseEvent $event
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->eventDispatcher->addListener("victoire_core.page_menu.global",
            array($this->pageMenu, 'addGlobal')
        );
        $this->eventDispatcher->addListener("victoire_core.page_menu.contextual",
            array($this->pageMenu, 'addContextual')
        );
        $this->eventDispatcher->addListener("victoire_core.template_menu.global",
            array($this->templateMenu, 'addGlobal')
        );
        $this->eventDispatcher->addListener("victoire_core.template_menu.contextual",
            array($this->templateMenu, 'addContextual')
        );
        $this->eventDispatcher->addListener("victoire_core.sitemap_menu.global",
            array($this->sitemapMenu, 'addGlobal')
        );
        $this->eventDispatcher->addListener("victoire_core.businessentitytemplate_menu.global",
            array($this->businessEntityTemplateMenuListener, 'addGlobal')
        );

        //the contextual menu of the business entity template must handle the page and the business entity template
        $this->eventDispatcher->addListener("victoire_core.businessEntityTemplate_menu.contextual",
            array($this->businessEntityTemplateMenuListener, 'addContextual')
        );
        $this->eventDispatcher->addListener("victoire_core.page_menu.contextual",
            array($this->businessEntityTemplateMenuListener, 'addContextual')
        );

        $this->eventDispatcher->dispatch('victoire_core.page_menu.global');
        $this->eventDispatcher->dispatch('victoire_core.template_menu.global');
        $this->eventDispatcher->dispatch('victoire_core.sitemap_menu.global');
        $this->eventDispatcher->dispatch('victoire_core.businessentitytemplate_menu.global');
    }
}
