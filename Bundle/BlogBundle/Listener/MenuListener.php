<?php
namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 *
 * @author Thomas Beaujean
 *
 */
class MenuListener
{
    protected $ed;
    protected $blogMenu;

    /**
     * Constructor
     *
     * @param EventDispatcher $ed
     * @param unknown $blogMenu
     */
    public function __construct($ed, $blogMenu)
    {
        $this->ed = $ed;
        $this->blogMenu = $blogMenu;
    }

    /**
     *
     * @param GetResponseEvent $event
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->ed->addListener("victoire_core.article_menu.global",
            array($this->blogMenu, 'addGlobal')
        );
        $this->ed->addListener("victoire_core.article_menu.contextual",
            array($this->blogMenu, 'addContextual')
        );

        $this->ed->dispatch('victoire_core.article_menu.global');
    }
}
