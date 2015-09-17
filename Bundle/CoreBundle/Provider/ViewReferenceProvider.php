<?php

namespace Victoire\Bundle\CoreBundle\Provider;


use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BusinessPageBundle\Builder\BusinessPageBuilder;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

/**
 * @property BusinessPageHelper businessPageHelper
 * @property BusinessPageBuilder businessPageBuilder
 */
class ViewReferenceProvider {

    protected $businessPageHelper;
    protected $businessPageBuilder;

    /**
     * @param BusinessPageHelper $businessPageHelper
     * @param BusinessPageBuilder $businessPageBuilder
     */
    function __construct(BusinessPageHelper $businessPageHelper, BusinessPageBuilder $businessPageBuilder)
    {
        $this->businessPageHelper = $businessPageHelper;
        $this->businessPageBuilder = $businessPageBuilder;
    }


    /**
     * @param [View] $views
     * @param EntityManager $em
     */
    public function getReferencableViews($views, EntityManager $em)
    {
        $referencableViews = [];
        if (!$views instanceof \Traversable && !is_array($views)) {
            $views = [$views];
        }

        foreach ($views as $view) {

            if ($view instanceof BusinessTemplate) {

                $entities = $this->businessPageHelper->getEntitiesAllowed($view, $em);

                // for each business entity
                foreach ($entities as $entity) {
                    $currentPattern = clone $view;
                    $page = $this->businessPageBuilder->generateEntityPageFromPattern($currentPattern, $entity, $em);
                    $this->businessPageBuilder->updatePageParametersByEntity($page, $entity);

                    $referencableViews[] = $page;

                    //I refresh this partial entity from em. If I don't do it, everytime I'll request this entity from em it'll be partially populated
                    $em->refresh($entity);
                }
            } else if ($view instanceof WebViewInterface) {
                $referencableViews[] = $view;
            }
        }

        return $referencableViews;
    }
} 
