<?php

namespace Victoire\Bundle\CoreBundle\Manager\Chain;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\BaseReferenceBuilder;
use Victoire\Bundle\CoreBundle\Manager\Interfaces\ReferenceBuilderInterface;

/**
 * Class ViewReferenceBuilderChain
 * @package Victoire\Bundle\CoreBundle\Manager\Chain
 */
class ViewReferenceBuilderChain
{
    private $viewsManagers;

    public function __construct()
    {
        $this->viewsManagers = array();
    }

    /**
     * add a view Manager
     * @param ReferenceBuilderInterface $viewManager
     */
    public function addViewManager(BaseReferenceBuilder $viewManager, $view)
    {
        $this->viewsManagers[$view] = $viewManager;
    }

    /**
     * @param View $view
     * @return ReferenceBuilderInterface
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
