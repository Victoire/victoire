<?php
namespace Victoire\Bundle\CoreBundle\Helper;

use Doctrine\Orm\EntityManager;
use Gedmo\Sluggable\Util\Urlizer;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter as BETParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\TemplateBundle\Entity\Template;

/**
 * Page helper
 * ref: victoire_core.view_helper
 */
class ViewHelper
{
    protected $parameterConverter = null;
    protected $businessEntityHelper = null;
    protected $bepHelper;
    protected $entityManager; // @doctrine.orm.entity_manager'
    protected $urlizer; // @gedmo.urlizer

    /**
     * Constructor
     * @param BETParameterConverter    $parameterConverter
     * @param BusinessEntityHelper     $businessEntityHelper
     * @param BusinessEntityPageHelper $bepHelper
     * @param EntityManager            $entityManager
     * @param ViewCacheHelper          $viewCacheHelper
     * @param Urlizer                  $urlizer
     */
    public function __construct(
        BETParameterConverter $parameterConverter,
        BusinessEntityHelper $businessEntityHelper,
        BusinessEntityPageHelper $bepHelper,
        EntityManager $entityManager,
        ViewCacheHelper $viewCacheHelper,
        Urlizer $urlizer
    )
    {
        $this->parameterConverter = $parameterConverter;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->businessEntityPageHelper = $bepHelper;
        $this->em = $entityManager;
        $this->viewCacheHelper = $viewCacheHelper;
        $this->urlizer = $urlizer;
    }

    //@todo Make it dynamic please
    protected $pageParameters = array(
        'name',
        'bodyId',
        'bodyClass',
        'slug',
        'url',
        'locale'
    );

    /**
     * This method get all views (BasePage and Template) in DB and return the references, including non persisted Business entity page (pattern and businessEntityName based)
     * @return array the computed views as array
     */
    public function getAllViewsReferences()
    {
        $viewsReferences = array();
        $schemaManager = $this->em->getConnection()->getSchemaManager();
        if ($schemaManager->tablesExist(array('vic_view')) == true) {
            //This query is not optimized because we need the property "businessEntityName" later, and it's only present in Pattern pages
            $views = $this->em->createQuery("SELECT v FROM VictoireCoreBundle:View v")->getResult();
        } else {
            $views = array();
        }

        foreach ($views as $view) {
            $viewsReferences = array_merge($viewsReferences, $this->buildViewReference($view));
        }

        return $viewsReferences;
    }

    /**
     * Generate update the page parameters with the entity
     *
     * @param BasePage $page
     * @param Entity   $entity
     */
    public function updatePageParametersByEntity(BusinessEntityPage $page, $entity)
    {
        //if no entity is provided
        if ($entity === null) {
            //we look for the entity of the page
            if ($page->getBusinessEntity() !== null) {
                $entity = $page->getBusinessEntity();
            }
        }

        //only if we have an entity instance
        if ($entity !== null) {
            $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);

            if ($businessEntity !== null) {

                $businessProperties = $this->businessEntityPageHelper->getBusinessProperties($businessEntity);

                //parse the business properties
                foreach ($businessProperties as $businessProperty) {
                    //parse of seo attributes
                    foreach ($this->pageParameters as $pageAttribute) {
                        $string = $this->getEntityAttributeValue($page, $pageAttribute);
                        $updatedString = $this->parameterConverter->setBusinessPropertyInstance($string, $businessProperty, $entity);
                        $this->setEntityAttributeValue($page, $pageAttribute, $updatedString);
                    }
                }
                $page->setSlug($this->urlizer->urlize($page->getName()));
            }
        }
    }

    /**
     * Get the content of an attribute of an entity given
     *
     * @param entity $entity
     * @param strin  $field
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($entity, $field)
    {
        $functionName = 'get'.ucfirst($field);

        $fieldValue = call_user_func(array($entity, $functionName));

        return $fieldValue;
    }

    /**
     * Update the value of the entity
     * @param Object $entity
     * @param string $field
     * @param string $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func(array($entity, $functionName), $value);
    }

    /**
     * compute the viewReference relative to a View + entity
     * @param View                $view
     * @param BusinessEntity|null $entity
     *
     * @return void
     */
    public function buildViewReference(View $view, $entity = null)
    {
        $viewsReferences = array();
        // if page is a pattern, compute it's bep
        if ($view instanceof BusinessEntityPagePattern) {

            if ($entity) {
                $currentPattern = clone $view;
                $page = $this->businessEntityPageHelper->generateEntityPageFromPattern($currentPattern, $entity);
                $this->updatePageParametersByEntity($page, $entity);
                $referenceId = $this->viewCacheHelper->getViewCacheId($view, $entity);
                $viewsReferences[$page->getUrl().$view->getLocale()] = array(
                    'id'              => $referenceId,
                    'url'             => $page->getUrl(),
                    'locale'          => $entity->getLocale(),
                    'viewId'          => $page->getTemplate()->getId(),
                    'entityId'        => $entity->getId(),
                    'entityNamespace' => $this->em->getClassMetadata(get_class($entity))->name,
                    'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
                );
            } else {

                $referenceId = $this->viewCacheHelper->getViewCacheId($view);
                $viewsReferences[$view->getUrl().$view->getLocale()] = array(
                    'id'              => $referenceId,
                    'url'             => $view->getUrl(),
                    'locale'          => $view->getLocale(),
                    'viewId'          => $view->getId(),
                    'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
                );
                $businessEntities = $this->businessEntityHelper->getBusinessEntities();


                foreach ($businessEntities as $businessEntity) {
                    $properties = $this->businessEntityPageHelper->getBusinessProperties($businessEntity);

                    //find businessEdietifiers of the current businessEntity
                    $selectableProperties = array('id');
                    foreach ($properties as $property) {
                        if ($property->getType() === 'businessParameter') {
                            $selectableProperties[] = $property->getEntityProperty();
                        }
                    }

                    // This query retrieve business entity object, without useless properties for performance optimisation
                    $entities = $this->em->getRepository($businessEntity->getClass())
                        ->createQueryBuilder('e')
                        ->addSelect('partial e.{'. implode(', ', $selectableProperties). '}')
                        ->getQuery()
                        ->getResult();

                    // for each business entity
                    foreach ($entities as $entity) {

                        // only if related pattern entity is the current entity
                        if ($view->getBusinessEntityName() === $businessEntity->getId()) {
                            $currentPattern = clone $view;
                            $page = $this->businessEntityPageHelper->generateEntityPageFromPattern($currentPattern, $entity);
                            $this->updatePageParametersByEntity($page, $entity);
                            $referenceId = $this->viewCacheHelper->getViewCacheId($view, $entity);
                            $viewsReferences[$page->getUrl().$view->getLocale()] = array(
                                'id'              => $referenceId,
                                'url'             => $page->getUrl(),
                                'locale'          => $entity->getLocale(),
                                'viewId'          => $page->getTemplate()->getId(),
                                'entityId'        => $entity->getId(),
                                'entityNamespace' => $this->em->getClassMetadata(get_class($entity))->name,
                                'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
                            );
                        }
                        //I detach this partial entity from em. If I don't do it, everytime I'll request this entity from em it'll be partially populated
                        $this->em->detach($entity);
                    }
                }
            }

        } elseif ($view instanceof BusinessEntityPage) {
            $referenceId = $this->viewCacheHelper->getViewCacheId($view);
            $viewsReferences[$view->getUrl().$view->getLocale()] = array(
                'id'              => $referenceId,
                'locale'          => $view->getLocale(),
                'viewId'          => $view->getId(),
                'patternId'       => $view->getTemplate()->getId(),
                'url'             => $view->getUrl(),
                'entityId'        => $view->getBusinessEntity()->getId(),
                'entityNamespace' => $this->em->getClassMetadata(get_class($view->getBusinessEntity()))->name,
                'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
            );
        } elseif ($view instanceof Template) {
            $referenceId = $this->viewCacheHelper->getViewCacheId($view);
            $viewsReferences[$referenceId.$view->getLocale()] = array(
                'id'              => $referenceId,
                'locale'          => $view->getLocale(),
                'viewId'          => $view->getId(),
                'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
            );
        } else {
            $referenceId = $this->viewCacheHelper->getViewCacheId($view);
            $viewsReferences[$view->getUrl().$view->getLocale()] = array(
                'id'              => $referenceId,
                'locale'          => $view->getLocale(),
                'viewId'          => $view->getId(),
                'url'             => $view->getUrl(),
                'viewNamespace'   => $this->em->getClassMetadata(get_class($view))->name,
            );
        }

        return $viewsReferences;

    }

    public function addTranslation(View $view, $templateName = null, $loopIndex = 0, $locale = null) 
    {
        if ($loopIndex === 0) {
            $locale = $view->getLocale();
            $this->em->detach($view);
        }
        $loopIndex+=1; 
        $template = null;
        if($view->getTemplate()) {
            $template = $view->getTemplate();
            if($template->getI18n()->getTranslation($locale)) {
                $template = $template->getI18n()->getTranslation($locale);
            } else {
                $templateName = $template->getName()."-".$locale; 
                $template = $this->addTranslation($template, $templateName, $loopIndex, $locale);
            }
        } else {
            $templateName = $view->getName().'-'.$locale;
        }

        $clonedView = $this->cloneView($view, $templateName);
        if($clonedView instanceof Template && $view->getTemplate()) {
           $clonedView->setTemplate($template);
           $template->setPages($clonedView);          
        }

        $i18n = $view->getI18n();
        $i18n->setTranslation($locale, $clonedView);

        $this->em->persist($clonedView);
        $this->em->flush();

        return $clonedView;
    }

    public function cloneView(View $view, $templateName = null)
    {
        $clonedView = clone $view;
        $widgetMapClone= $clonedView->getWidgetMap(false);
        $arrayMapOfWidgetMap = array();

        if(null !== $templateName) 
        {
            $clonedView->setName($templateName);
        }
        
        $clonedView->setId(null);
        $this->em->persist($clonedView);

        foreach ($clonedView->getWidgets() as $widgetKey => $widgetVal) {
            $clonedWidget = clone $widgetVal;
            $clonedWidget->setId(null);
            $clonedWidget->setView($clonedView);
            $this->em->persist($clonedWidget);
            $arrayMapOfWidgetMap[$widgetVal->getId()] = $clonedWidget;
        }

        $this->em->persist($clonedView);
        $this->em->flush();

        foreach($widgetMapClone as $wigetSlotCloneKey => $widgetSlotCloneVal) {
            foreach($widgetSlotCloneVal as $widgetMapItemKey => $widgetMapItemVal) {
                if(isset($arrayMapOfWidgetMap[$widgetMapItemVal['widgetId']])) {
                    $widgetId = $arrayMapOfWidgetMap[$widgetMapItemVal['widgetId']]->getId();
                    $widgetMapItemVal['widgetId'] = $widgetId;
                    $widgetMapClone[$wigetSlotCloneKey][$widgetMapItemKey] = $widgetMapItemVal;
                }
            }
        } 
        
        $clonedView->setSlots(array()); 
        $clonedView->setWidgetMap($widgetMapClone); 
        
        $this->em->persist($clonedView);
        $this->em->flush();

        return $clonedView;
        
    }

}
