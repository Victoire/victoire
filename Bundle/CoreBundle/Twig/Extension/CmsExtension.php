<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPagePattern;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Handler\WidgetExceptionHandler;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\CoreBundle\Helper\ViewCacheHelper;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Victoire\Bundle\WidgetBundle\Entity\Widget;
use Victoire\Bundle\WidgetBundle\Renderer\WidgetRenderer;

/**
 * CmsExtension extends Twig with view capabilities.
 *
 * service: victoire_core.twig.cms_extension
 */
class CmsExtension extends \Twig_Extension_Core
{
    protected $widgetRenderer;
    protected $templating;
    protected $securityContext;
    protected $entityManager;
    protected $widgetMapBuilder;
    protected $widgetExceptionHandler;
    protected $currentViewHelper;
    protected $twig;

    /**
     * Constructor
     *
     * @param WidgetRenderer         $widgetRenderer
     * @param TemplateMapper         $templating
     * @param SecurityContext        $securityContext
     * @param EntityManager          $entityManager
     * @param WidgetExceptionHandler $widgetExceptionHandler
     * @param CurrentViewHelper      $currentViewHelper
     * @param ViewCacheHelper        $viewCacheHelper
     * @param \Twig_Environment      $twig
     */
    public function __construct(
        WidgetRenderer $widgetRenderer,
        TemplateMapper $templating,
        SecurityContext $securityContext,
        EntityManager $entityManager,
        WidgetExceptionHandler $widgetExceptionHandler,
        CurrentViewHelper $currentViewHelper,
        ViewCacheHelper $viewCacheHelper,
        \Twig_Environment $twig
    ) {
        $this->widgetRenderer = $widgetRenderer;
        $this->templating = $templating;
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->widgetExceptionHandler = $widgetExceptionHandler;
        $this->currentViewHelper = $currentViewHelper;
        $this->viewCacheHelper = $viewCacheHelper;
        $this->twig = $twig;
    }

    /**
     * register twig functions
     *
     * @return array The list of extensions
     */
    public function getFunctions()
    {
        return array(
            'cms_widget_unlink_action'   => new \Twig_Function_Method($this, 'cmsWidgetUnlinkAction', array('is_safe' => array('html'))),
            'cms_slot_widgets'           => new \Twig_Function_Method($this, 'cmsSlotWidgets', array('is_safe' => array('html'))),
            'cms_slot_actions'           => new \Twig_Function_Method($this, 'cmsSlotActions', array('is_safe' => array('html'))),
            'cms_widget'                 => new \Twig_Function_Method($this, 'cmsWidget', array('is_safe' => array('html'))),
            'cms_widget_extra_css_class' => new \Twig_Function_Method($this, 'cmsWidgetExtraCssClass', array('is_safe' => array('html'))),
            'is_business_entity_allowed' => new \Twig_Function_Method($this, 'isBusinessEntityAllowed', array('is_safe' => array('html'))),
        );
    }

    /**
     * register twig filters
     *
     * @return array The list of filters
     */
    public function getFilters()
    {
        return array(
            'hash' => new \Twig_Filter_Method($this, 'hash'),
            'date' => new \Twig_Filter_Method($this, 'twigVicDateFormatFilter'),

        );
    }

    /**
     * get extension name
     *
     * @return string The name
     */
    public function getName()
    {
        return 'cms';
    }

    /**
     * render unlink action for a widgetId
     *
     * @param integer $widgetId The widgetId to unlink
     *
     * @return string the widget unlink action
     */
    public function cmsWidgetUnlinkAction($widgetId, $view)
    {
        $viewReference = $reference = $this->viewCacheHelper->getReferenceByParameters(
            array('viewId' => $view->getId())
        );
        $view->setReference($viewReference);

        return $this->widgetRenderer->renderUnlinkActionByWidgetId($widgetId, $view);
    }

    /**
     * render all widgets in a slot
     *
     * @param string $slotId
     * @param string $slotOptions
     *
     * @return string HTML markup of the widget with action button if needed
     */
    public function cmsSlotWidgets($slotId, $slotOptions = array())
    {
        $currentView = $this->currentViewHelper->getCurrentView();
        //services
        $em = $this->entityManager;

        $result = "";
        $slotOptions = $this->widgetRenderer->computeOptions($slotId, $slotOptions);
        $slotNewContentButton = $this->isRoleVictoireGranted() ? $this->widgetRenderer->renderActions($slotId, $slotOptions): '';

        if (!empty($currentView->getWidgetMap()[$slotId])) {
            //parse the widget maps
            foreach ($currentView->getWidgetMap()[$slotId] as $widgetMap) {
                $widget = null;
                try {
                    //get the widget id
                    $widgetId = $widgetMap->getWidgetId();

                    if (!$widgetMap->isAsynchronous()) {

                        //get the widget
                        $widgetRepo = $em->getRepository('VictoireWidgetBundle:Widget');
                        $widget = $widgetRepo->findOneById($widgetId);

                        //test widget
                        if ($widget === null) {
                            throw new \Exception('The widget with the id:['.$widgetId.'] was not found.');
                        }

                        //render this widget
                        $result .= $this->cmsWidget($widget);
                    } else {
                        $result .= $this->widgetRenderer->prepareAsynchronousRender($widgetId);
                    }
                    $result .= $slotNewContentButton;
                } catch (\Exception $ex) {
                    $result .= $this->widgetExceptionHandler->handle($ex, $currentView, $widget, $widgetId);
                }
            }
        }
        //the container for the slot
        $ngSlotControllerName = 'slot'.$slotId.'Controller';
        $ngInitLoadActions = $this->isRoleVictoireGranted() ? sprintf('ng-init=\'%s.init("%s", %s)\'', $ngSlotControllerName, $slotId, json_encode($slotOptions)) : '';
        $result = sprintf(
            '<div class="vic-slot" data-name="%s" id="vic-slot-%s" ng-controller="SlotController as %s" %s>%s%s</div>',
            $slotId,
            $slotId,
            $ngSlotControllerName,
            $ngInitLoadActions,
            $slotNewContentButton,
            $result
        );

        return $result;
    }

    /**
     * Render a widget
     * @param Widget $widget
     *
     * @return string
     */
    public function cmsWidget($widget)
    {
        $widget->setCurrentView($this->currentViewHelper->getCurrentView());

        try {
            $response = $this->widgetRenderer->renderContainer($widget, $widget->getCurrentView());
        } catch (\Exception $ex) {
            $response = $this->widgetExceptionHandler->handle($ex, $widget);
        }

        return $response;
    }

    /**
     * hash some string with given algorithm
     *
     * @param string $value     The string to hash
     * @param string $algorithm The algorithm we have to use to hash the string
     *
     * @return string
     *
     */
    public function hash($value, $algorithm = "md5")
    {
        try {
            return hash($algorithm, $value);
        } catch (\Exception $e) {
            error_log('Please check that the '.$algorithm.' does exists because it failed when trying to run. We are expecting a valid algorithm such as md5 or sha512 etc. ['.$e->getMessage().']');

            return $value;
        }
    }

    /**
     * Converts a date to the given format.
     * @param string              $format   A format
     * @param DateTimeZone|string $timezone A timezone
     *
     * <pre>
     *   {{ post.published_at|date("m/d/Y") }}
     * </pre>
     *
     *
     * @return string The formatted date
     */
    public function twigVicDateFormatFilter($value, $format = 'F j, Y H:i', $timezone = null)
    {
        try {
            $result = twig_date_format_filter($this->twig, $value, $format, $timezone);
        } catch (\Exception $e) {
            return $value;
        }

        return $result;
    }

    /**
     * Get the extra class for this kind of widget
     *
     * @param Widget $widget
     *
     * @return string The extra classes
     */
    public function cmsWidgetExtraCssClass(Widget $widget)
    {
        $extraClass = $this->widgetRenderer->getExtraCssClass($widget);

        return $extraClass;
    }

    /**
     * Is the business entity type allowed for the widget and the view context
     *
     * @param string $formEntityName The business entity name
     * @param View   $view           The view
     *
     * @return boolean Does the form allows this kind of business entity in this view
     */
    public function isBusinessEntityAllowed($formEntityName, View $view)
    {
        //the result
        $isBusinessEntityAllowed = false;

        if ($view instanceof BusinessEntityPagePattern || $view instanceof BusinessEntityPage) {
            //are we using the same business entity
            if ($formEntityName === $view->getBusinessEntityId()) {
                $isBusinessEntityAllowed = true;
            }
        }

        return $isBusinessEntityAllowed;
    }

    /**
     * Does the current user have the role victoire granted
     *
     * @return boolean
     */
    protected function isRoleVictoireGranted()
    {
        $isGranted = false;

        if ($this->securityContext->isGranted('ROLE_VICTOIRE')) {
            $isGranted = true;
        }

        return $isGranted;
    }

    /**
     * Does the current user have the role victoire granted
     *
     * @return boolean
     */
    protected function isRoleVictoireDeveloperGranted()
    {
        $isGranted = false;

        if ($this->securityContext->isGranted('ROLE_VICTOIRE_DEVELOPER')) {
            $isGranted = true;
        }

        return $isGranted;
    }
}
