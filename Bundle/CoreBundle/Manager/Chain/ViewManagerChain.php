<?php

namespace Victoire\Bundle\CoreBundle\Manager\Chain;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\ViewManagerInterface;

/**
 * Class ViewManagerChain
 * @package Victoire\Bundle\CoreBundle\Manager\Chain
 */
class ViewManagerChain
{
    private $viewsManagers;

    public function __construct()
    {
        $this->viewsManagers = array();
    }

    /**
     * add a view Manager
     * @param ViewManagerInterface $viewManager
     */
    public function addViewManager(ViewManagerInterface $viewManager, $view)
    {
        $this->viewsManagers[$view] = $viewManager;
    }

    /**
     * @param View $view
     * @return ViewManagerInterface
     */
    public function getViewManager(View $view)
    {
        if(array_key_exists($viewClass = get_class($view), $this->viewsManagers))
        {
            return $this->viewsManagers[$viewClass];
        }
        throw new ServiceNotFoundException('No view manager found for ' . $viewClass);
    }
}
