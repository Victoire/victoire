<?php
namespace Victoire\Bundle\BlogBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;


class MenuListener
{

    protected $ed;
    protected $blogMenu;

    public function __construct($ed, $blogMenu)
    {
        $this->ed = $ed;
        $this->blogMenu = $blogMenu;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        $this->ed->addListener("victoire_cms.article_menu.global",
            array($this->blogMenu, 'addGlobal')
        );
        $this->ed->addListener("victoire_cms.article_menu.contextual",
            array($this->blogMenu, 'addContextual')
        );

        $this->ed->dispatch('victoire_cms.article_menu.global');

    }

}
