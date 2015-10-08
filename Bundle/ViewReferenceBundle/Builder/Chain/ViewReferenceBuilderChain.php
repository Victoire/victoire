<?php

namespace Victoire\Bundle\ViewReferenceBundle\Builder\Chain;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\ViewReferenceBundle\Builder\BaseReferenceBuilder;

/**
 * Class ViewReferenceBuilderChain.
 */
class ViewReferenceBuilderChain
{
    private $viewsReferenceBuilders;

    public function __construct()
    {
        $this->viewsReferenceBuilders = [];
    }

    /**
     * add a view Manager.
     *
     * @param BaseReferenceBuilder $viewManager
     * @param string               $viewClassName
     */
    public function addViewReferenceBuilder(BaseReferenceBuilder $viewManager, $viewClassName)
    {
        $this->viewsReferenceBuilders[$viewClassName] = $viewManager;
    }

    /**
     * @param View $view
     *
     * @return BaseReferenceBuilder
     */
    public function getViewReferenceBuilder(View $view)
    {
        if (array_key_exists($viewClass = get_class($view), $this->viewsReferenceBuilders)) {
            return $this->viewsReferenceBuilders[$viewClass];
        }
        throw new ServiceNotFoundException('No view reference builder found for '.$viewClass);
    }
}
