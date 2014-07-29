<?php
namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\SecurityContext;
use Victoire\Bundle\BusinessEntityTemplateBundle\Listener\BusinessEntityTemplateMenuListener;

/**
 * This class add items in admin menu
 *
 * @package Victoire/Menu
 * @author  Leny Bernard <leny@appventus.com>
 *
 *
 **/
class MenuDispatcher
{
    protected $eventDispatcher;

    /**
     * Construct function to include eventDispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param SecurityContext          $securityContext
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, SecurityContext $securityContext)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->securityContext = $securityContext;
    }

    /**
     * Dispatch event to build the Victoire's global menu items
     *
     * @param GetResponseEvent $event
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        //IF VICTOIRE
        if ($this->securityContext->getToken() && $this->securityContext->isGranted('ROLE_VICTOIRE')) {
            $this->eventDispatcher->dispatch('victoire_core.build_menu', $event);
        }
        // $this->eventDispatcher->addListener("victoire_core.page_menu.global",
        //     array($this->pageMenu, 'addGlobal')
        // );
        // $this->eventDispatcher->addListener("victoire_core.page_menu.contextual",
        //     array($this->pageMenu, 'addContextual')
        // );
        // $this->eventDispatcher->addListener("victoire_core.businessEntityTemplate_menu.contextual",
        //     array($this->pageMenu, 'addContextual')
        // );
        // $this->eventDispatcher->addListener("victoire_core.template_menu.global",
        //     array($this->templateMenu, 'addGlobal')
        // );
        // $this->eventDispatcher->addListener("victoire_core.template_menu.contextual",
        //     array($this->templateMenu, 'addContextual')
        // );
        // $this->eventDispatcher->addListener("victoire_core.sitemap_menu.global",
        //     array($this->sitemapMenu, 'addGlobal')
        // );
        // $this->eventDispatcher->addListener("victoire_core.businessentitytemplate_menu.global",
        //     array($this->businessEntityTemplateMenuListener, 'addGlobal')
        // );

        // //the contextual menu of the business entity template must handle the page and the business entity template
        // $this->eventDispatcher->addListener("victoire_core.businessEntityTemplate_menu.contextual",
        //     array($this->businessEntityTemplateMenuListener, 'addContextual')
        // );
        // $this->eventDispatcher->addListener("victoire_core.page_menu.contextual",
        //     array($this->businessEntityTemplateMenuListener, 'addContextual')
        // );

        // $this->eventDispatcher->dispatch('victoire_core.page_menu.global');
        // $this->eventDispatcher->dispatch('victoire_core.template_menu.global');
        // $this->eventDispatcher->dispatch('victoire_core.sitemap_menu.global');
        // $this->eventDispatcher->dispatch('victoire_core.businessentitytemplate_menu.global');
    }
}
