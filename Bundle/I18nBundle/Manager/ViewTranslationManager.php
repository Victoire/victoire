<?php

namespace Victoire\Bundle\I18nBundle\Manager;

use Doctrine\Orm\EntityManager;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * Page helper
 * ref: victoire_i18n.view_translation_manager.
 */
class ViewTranslationManager
{
    protected $parameterConverter;
    protected $businessEntityHelper;
    protected $em;
    protected $viewReferenceBuilder;
    protected $viewReferenceHelper;
    protected $viewReferenceProvider;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
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
                $templateName = $template->getName().'-'.$locale;
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
     * @param $templateName the future name of the clone
     *
     * this methods allows you to clone a view and its widgets and also the widgetmap
     */
    public function cloneView(View $view, $templateName = null)
    {
        $clonedView = clone $view;
        $this->em->refresh($view);
        $widgetMapClone = $clonedView->getWidgetMap(false);
        $arrayMapOfWidgetMap = [];
        if (null !== $templateName) {
            $clonedView->setName($templateName);
        }

        $clonedView->setId(null);
        $this->em->persist($clonedView);

        if (!$clonedView instanceof BusinessTemplate) {
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

            $clonedView->setSlots([]);
            $clonedView->setWidgetMap($widgetMapClone);
            $this->em->persist($clonedView);
            $this->em->flush();
        }

        return $clonedView;
    }
}
