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
    private $viewsReferenceBuilders;

    public function __construct()
    {
        $this->viewsReferenceBuilders = array();
    }

    /**
     * add a view Manager
     * @param ReferenceBuilderInterface $viewManager
     */
    public function addViewReferenceBuilder(BaseReferenceBuilder $viewManager, $view)
    {
        $this->viewsReferenceBuilders[$view] = $viewManager;
    }

    /**
     * @param View $view
     * @return ReferenceBuilderInterface
     */
    public function getViewReferenceBuilder(View $view)
    {
        if(array_key_exists($viewClass = get_class($view), $this->viewsReferenceBuilders))
        {
            return $this->viewsReferenceBuilders[$viewClass];
        }
        throw new ServiceNotFoundException('No view reference builder found for ' . $viewClass);
    }
}
