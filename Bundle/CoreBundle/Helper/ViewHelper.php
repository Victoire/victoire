<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Doctrine\Orm\EntityManager;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter as BETParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\BusinessEntityPageBundle\Helper\BusinessEntityPageHelper;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\Chain\ViewManagerChain;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Widget\LayoutBundle\Entity\WidgetLayout;

/**
 * Page helper
 * ref: victoire_core.view_helper
 */
class ViewHelper
{
    protected $parameterConverter;
    protected $businessEntityHelper;
    protected $businessEntityPageHelper;
    protected $em;
    protected $viewCacheHelper;

    /**
     * Constructor
     * @param BETParameterConverter    $parameterConverter
     * @param BusinessEntityHelper     $businessEntityHelper
     * @param BusinessEntityPageHelper $businessEntityPageHelper
     * @param EntityManager            $entityManager
     * @param ViewCacheHelper          $viewCacheHelper
     * @param ViewManagerChain         $$viewManagerChain
     */
    public function __construct(
        BETParameterConverter $parameterConverter,
        BusinessEntityHelper $businessEntityHelper,
        BusinessEntityPageHelper $businessEntityPageHelper,
        EntityManager $entityManager,
        ViewCacheHelper $viewCacheHelper,
        ViewManagerChain $viewManagerChain
    ) {
        $this->parameterConverter = $parameterConverter;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->businessEntityPageHelper = $businessEntityPageHelper;
        $this->em = $entityManager;
        $this->viewCacheHelper = $viewCacheHelper;
        $this->viewManagerChain = $viewManagerChain;
    }

    //@todo Make it dynamic please
    protected $pageParameters = array(
        'name',
        'bodyId',
        'bodyClass',
        'slug',
        'url',
        'locale',
    );

    public function buildViewsReferences($views)
    {
        $viewsReferences = array();
        foreach ($views as $view) {
            $viewsReferences = array_merge($viewsReferences, $this->buildViewReference($view));
            $this->em->refresh($view);
        }

        $this->cleanVirtualViews($viewsReferences);

        return $viewsReferences;
    }

    public function cleanVirtualViews(&$viewsReferences)
    {
        foreach ($viewsReferences as $viewReference) {
            // If viewReference is a persisted page, we want to clean virtual BEPs
            if ($viewReference['viewNamespace'] == 'Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage') {
                array_walk($viewsReferences, function ($_viewReference, $key) use ($viewReference, &$viewsReferences) {
                    if ($_viewReference['viewNamespace'] == 'Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern'
                        && !empty($_viewReference['entityNamespace']) && $_viewReference['entityNamespace'] == $viewReference['entityNamespace']
                        && !empty($_viewReference['entityId']) && $_viewReference['entityId'] == $viewReference['entityId']) {
                            unset($viewsReferences[$key]);
                    }
                });
            }
        }

    }
    /**
     * This method get all views (BasePage and Template) in DB and return the references, including non persisted Business entity page (pattern and businessEntityId based)
     * @return array the computed views as array
     */
    public function getAllViewsReferences()
    {
        $viewsReferences = $this->viewCacheHelper->convertXmlCacheToArray();

        return $viewsReferences;
    }

    /**
     * compute the viewReference relative to a View + entity
     * @param View                $view
     * @param BusinessEntity|null $entity
     *
     * @return array
     */
    public function buildViewReference(View $view, $entity = null)
    {
        $viewManager = $this->viewManagerChain->getViewManager($view);
        return $viewManager->buildReference($view, $entity);
    }

    /**
     * @param View $view, the view to translatate
     * @param $templatename the new name of the view
     * @param $loopindex the current loop of iteration in recursion
     * @param $locale the target locale to translate view
     *
     * this methods allow you to add a translation to any view
     * recursively to its subview
     */
    public function addTranslation(View $view, $viewName = null, $locale)
    {
        $template = null;
        if ($view->getTemplate()) {
            $template = $view->getTemplate();
            if ($template->getI18n()->getTranslation($locale)) {
                $template = $template->getI18n()->getTranslation($locale);
            } else {
                $templateName = $template->getName()."-".$locale;
                $this->em->refresh($view);
                $template = $this->addTranslation($template, $templateName, $locale);
            }
        }
        $view->setLocale($locale);
        $view->setTemplate($template);
        $clonedView = $this->cloneView($view, $viewName, $locale);
        if ($clonedView instanceof BasePage && $view->getTemplate()) {
            $template->addPage($clonedView);
        }
        $i18n = $view->getI18n();
        $i18n->setTranslation($locale, $clonedView);
        $this->em->persist($clonedView);
        $this->em->refresh($view);
        $this->em->flush();

        return $clonedView;
    }

    /**
     * @param View $view
     * @param $etmplateName the future name of the clone
     *
     * this methods allows you to clone a view and its widgets and also the widgetmap
     *
     */
    public function cloneView(View $view, $templateName = null)
    {
        $clonedView = clone $view;
        $this->em->refresh($view);
        $widgetMapClone = $clonedView->getWidgetMap(false);
        $arrayMapOfWidgetMap = array();
        if (null !== $templateName) {
            $clonedView->setName($templateName);
        }

        $clonedView->setId(null);
        $this->em->persist($clonedView);

        if ($clonedView instanceof BusinessEntityPagePattern) {
            $clonedView = $this->cloneBusinessEntityPagePattern($clonedView);
        } else {
            $widgetLayoutSlots = [];
            $newWidgets = [];
            foreach ($clonedView->getWidgets() as $widgetKey => $widgetVal) {
                $clonedWidget = clone $widgetVal;
                $clonedWidget->setId(null);
                $clonedWidget->setView($clonedView);
                $this->em->persist($clonedWidget);
                $newWidgets[] = $clonedWidget;
                $arrayMapOfWidgetMap[$widgetVal->getId()] = $clonedWidget;
                if ($widgetVal instanceof WidgetLayout) {
                    $id = $widgetVal->getId();
                    $widgetLayoutSlots[$id] = $clonedWidget;
                }
            }
            $clonedView->setWidgets($newWidgets);
            $this->em->persist($clonedView);
            $this->em->flush();
            $widgetSlotMap = [];
            foreach ($widgetLayoutSlots as $_id => $_widget) {
                foreach ($clonedView->getWidgets() as $_clonedWidget) {
                    if (preg_match('/^'.$_id.'_(.)/', $_clonedWidget->getSlot(), $matches)) {
                        $newSlot = $_widget->getId().'_'.$matches[1];
                        $oldSlot = $_clonedWidget->getSlot();
                        $_clonedWidget->setSlot($newSlot);
                        $widgetSlotMap[$oldSlot] = $newSlot;
                    }
                }
            }

            $this->em->flush();
            foreach ($widgetMapClone as $wigetSlotCloneKey => $widgetSlotCloneVal) {
                foreach ($widgetSlotCloneVal as $widgetMapItemKey => $widgetMapItemVal) {
                    if (isset($arrayMapOfWidgetMap[$widgetMapItemVal['widgetId']])) {
                        $widgetId = $arrayMapOfWidgetMap[$widgetMapItemVal['widgetId']]->getId();
                        $widgetMapItemVal['widgetId'] = $widgetId;
                        if (array_key_exists($wigetSlotCloneKey, $widgetSlotMap)) {
                            $wigetSlotCloneKey = $widgetSlotMap[$wigetSlotCloneKey];
                        }
                        $widgetMapClone[$wigetSlotCloneKey][$widgetMapItemKey] = $widgetMapItemVal;
                    }
                }
            }

            $clonedView->setSlots(array());
            $clonedView->setWidgetMap($widgetMapClone);
            $this->em->persist($clonedView);
            $this->em->flush();
        }

        return $clonedView;
    }

    /**
     * @param BusinessEntityPagePattern $view
     * @param $etmplateName the future name of the clone
     *
     * this methods allows you to clone a BusinessEntityPagePattern
     *
     */
    protected function cloneBusinessEntityPagePattern(BusinessEntityPagePattern $view)
    {
        $businessEntityId = $view->getBusinessEntityId();
        $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($businessEntityId);
        $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
    }

}
