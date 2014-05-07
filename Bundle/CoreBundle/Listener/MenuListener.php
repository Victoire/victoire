<?php
namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

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

    /**
     * Construct function to include eventDispatcher, pageMenu, TemplateMenu, SiteMapMenu
     *
     * @return void
     * TODO We should tag menu listeners to dynamically get them iof pass them as arg
     **/
    public function __construct($eventDispatcher, $pageMenu, $templateMenu, $sitemapMenu)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->pageMenu = $pageMenu;
        $this->templateMenu = $templateMenu;
        $this->sitemapMenu = $sitemapMenu;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        $this->eventDispatcher->addListener("victoire_cms.page_menu.global",
            array($this->pageMenu, 'addGlobal')
        );
        $this->eventDispatcher->addListener("victoire_cms.page_menu.contextual",
            array($this->pageMenu, 'addContextual')
        );
        $this->eventDispatcher->addListener("victoire_cms.template_menu.global",
            array($this->templateMenu, 'addGlobal')
        );
        $this->eventDispatcher->addListener("victoire_cms.template_menu.contextual",
            array($this->templateMenu, 'addContextual')
        );
        $this->eventDispatcher->addListener("victoire_cms.sitemap_menu.global",
            array($this->sitemapMenu, 'addGlobal')
        );

        $this->eventDispatcher->dispatch('victoire_cms.page_menu.global');
        $this->eventDispatcher->dispatch('victoire_cms.template_menu.global');
        $this->eventDispatcher->dispatch('victoire_cms.sitemap_menu.global');


    }

}
