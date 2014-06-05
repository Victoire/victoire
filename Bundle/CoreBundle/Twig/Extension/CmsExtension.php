<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Victoire\Bundle\CoreBundle\Menu\MenuManager;
use Victoire\Bundle\CoreBundle\Widget\Managers\WidgetManager;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Symfony\Component\Security\Core\SecurityContext;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;
use Victoire\Bundle\CoreBundle\Entity\Widget;
use Victoire\Bundle\CoreBundle\Form\WidgetType;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\CoreBundle\Helper\WidgetHelper;
use Victoire\Bundle\PageBundle\WidgetMap\WidgetMapBuilder;

/**
 * PageExtension extends Twig with page capabilities.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CmsExtension extends \Twig_Extension
{
    protected $widgetManager;
    protected $templating;
    protected $securityContext;
    protected $widgetHelper;
    protected $widgetMapBuilder;

    /**
     * Constructor
     *
     * @param WidgetManager    $widgetManager
     * @param TemplateMapper   $templating
     * @param SecurityContext  $securityContext
     * @param WidgetHelper     $widgetHelper
     * @param WidgetMapBuilder $widgetMapBuilder
     */
    public function __construct(WidgetManager $widgetManager, TemplateMapper $templating, SecurityContext $securityContext, WidgetHelper $widgetHelper, WidgetMapBuilder $widgetMapBuilder)
    {
        $this->widgetManager = $widgetManager;
        $this->templating = $templating;
        $this->securityContext = $securityContext;
        $this->widgetHelper = $widgetHelper;
        $this->widgetMapBuilder = $widgetMapBuilder;
    }

    /**
     * register twig functions
     *
     * @return array The list of extensions
     */
    public function getFunctions()
    {
        return array(
            'cms_widget_actions'         => new \Twig_Function_Method($this, 'cmsWidgetActions', array('is_safe' => array('html'))),
            'cms_slot_widgets'           => new \Twig_Function_Method($this, 'cmsSlotWidgets', array('is_safe' => array('html'))),
            'cms_slot_actions'           => new \Twig_Function_Method($this, 'cmsSlotActions', array('is_safe' => array('html'))),
            'cms_widget'                 => new \Twig_Function_Method($this, 'cmsWidget', array('is_safe' => array('html'))),
            'cms_page'                   => new \Twig_Function_Method($this, 'cmsPage', array('is_safe' => array('html'))),
            'cms_widget_legacy'          => new \Twig_Function_Method($this, 'cmsWidgetLegacy', array('is_safe' => array('html'))),
            'cms_widget_extra_css_class' => new \Twig_Function_Method($this, 'cmsWidgetExtraCssClass', array('is_safe' => array('html'))),
            'is_business_entity_allowed' => new \Twig_Function_Method($this, 'isBusinessEntityAllowed', array('is_safe' => array('html'))),
            'cms_widget_title'           => new \Twig_Function_Method($this, 'cmsWidgetTitle', array('is_safe' => array('html'))),
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
     * render actions for a widget
     *
     * @return string the widget actions (buttons edit, move and delete)
     */
    public function cmsWidgetActions($widget)
    {
        return $this->widgetManager->renderWidgetActions($widget);
    }

    /**
     * render all widgets in a slot
     *
     * @return string HTML markup of the widget with action button if needed
     */

    /**
     *
     * @param BasePage $page
     * @param unknown $slot
     * @param string $addContainer
     * @param string $entity
     * @return string
     */
    public function cmsSlotWidgets(BasePage $page, $slotId, $addContainer = true, $entity = null)
    {
        //services
        $widgetHelper = $this->widgetHelper;
        $widgetMapBuilder = $this->widgetMapBuilder;

        $result = "";

        if ($this->isRoleVictoireGranted()) {
            $result .= $this->widgetManager->renderActions($slotId, $page, true);
        }

        //get the widget map computed with the parent
        $widgetMaps = $widgetMapBuilder->computeCompleteWidgetMap($page, $slotId);

        //parse the widget maps
        foreach ($widgetMaps as $widgetMap) {
            //get the widget id
            $widgetId = $widgetMap->getWidgetId();

            //get the widget
            $widget = $widgetHelper->findOneById($widgetId);

            //test widget
            if ($widget === null) {
                throw new \Exception('The widget with the id:['.$widgetId.'] was not found.');
            }

            //the mode of display of the widget
            $mode = $widget->getMode();

            //in the business entity mode, we override the entity of the widget
            if ($mode === Widget::MODE_BUSINESS_ENTITY) {
                //set the entity for the widget
                $widget->setEntity($entity);
            }

            //render this widget
            $result .= $this->cmsWidget($widget, $addContainer);
        }

        if ($addContainer) {
            //the container for the slot
            $result = "<div class='vic-slot' id='vic-slot-".$slotId."'>".$result."</div>";
        }

        return $result;
    }

    /**
     * render all slot actions
     * @param Page   $page The current page
     * @param string $slot The current slot
     *
     * @return string HTML markup of the actions
     */
    public function cmsSlotActions($page, $slot)
    {
        return $this->widgetManager->renderActions($slot, $page);
    }

    /**
     *
     * @param unknown $widget
     * @param string $addContainer
     * @param string $entity
     * @return unknown
     */
    public function cmsWidget($widget, $addContainer = true)
    {
        $response = $this->widgetManager->render($widget, $addContainer);

        return $response;
    }

    /**
     * render all widgets for a page
     */
    public function cmsPage(BasePage $page)
    {
        return $this->templating->render(
            'VictoireCoreBundle:Layout:' . $page->getLayout(). '.html.twig',
            array('page' => $page)
        );

    }

    /**
     * hash some string with given algorithm
     * @param string $value     The string to hash
     * @param string $algorithm The algorithm we have to use to hash the string
     *
     */
    public function hash($value, $algorithm = "md5")
    {
        try {
            return hash($algorithm, $value);
        } catch (Exception $e) {
            error_log('Please check that the '.$algorithm.' does exists because it failed when trying to run. We are expecting a valid algorithm such as md5 or sha512 etc.');

            return $value;
        }
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
        $extraClass = $this->widgetManager->getExtraCssClass($widget);

        return $extraClass;
    }

    /**
     * Is the business entity type allowed for the widget and the page context
     *
     * @param string   $formEntityName The business entity name
     * @param BasePage $page           The page
     *
     * @return boolean Does the form allows this kind of business entity in this page
     */
    public function isBusinessEntityAllowed($formEntityName, Page $page)
    {
        //the result
        $isBusinessEntityAllowed = false;

        //get the page that is a business entity template page (parent included)
        $businessEntityTemplatePage = $page->getBusinessEntityTemplateLegacyPage();

        //if there is a page
        if ($businessEntityTemplatePage !== null) {
            //and a businessEntity name is given
            if ($formEntityName !== null) {
                //we check that the twi matches
                $businessEntityTemplate = $businessEntityTemplatePage->getBusinessEntityTemplate();

                //the business entity linked to the page template
                $pageBusinessEntity = $businessEntityTemplate->getBusinessEntityId();

                //are we using the same business entity
                if ($formEntityName === $pageBusinessEntity) {
                    $isBusinessEntityAllowed = true;
                }
            }
        }

        return $isBusinessEntityAllowed;
    }

    /**
     * If the widget is a legacy, we add the widget-legacy css class to the div
     *
     * @param Widget $widget The widget displayed
     * @param Page   $page   The page
     * @throws \Exception
     * @return string
     */
    public function cmsWidgetLegacy(Widget $widget, $page)
    {
        //the css class used
        $cssClass = '';

        //the page context was given
        if ($page !== null) {
            //the page of the widget is not the current page
            if ($widget->getPageId() !== $page->getId()) {
                $cssClass = 'vic-widget-legacy';
            }
        }

        return $cssClass;
    }

    /**
     * Get the title for a widget
     *
     * @param Widget $widget
     *
     * @return string The title text
     */
    public function cmsWidgetTitle(Widget $widget)
    {
        $title = '';

        if ($this->isRoleVictoireGranted()) {
            //the title markup
            $title = 'title="';

            //the description of the widget
            $description = $widget->getType().' - '.$widget->getMode();
            //add the description to the title
            $title .= $description;

            //close the markup
            $title .= '"';
        }

        return $title;
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
}
