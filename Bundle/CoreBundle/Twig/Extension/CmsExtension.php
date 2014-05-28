<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Victoire\Bundle\CoreBundle\Menu\MenuManager;
use Victoire\Bundle\CoreBundle\Widget\Managers\WidgetManager;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;
use Victoire\Bundle\CoreBundle\Entity\Widget;

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
    protected $session;

    /**
     * contructor
     */
    public function __construct(WidgetManager $widgetManager, TemplateMapper $templating, SecurityContext $securityContext, $session)
    {
        $this->widgetManager = $widgetManager;
        $this->templating = $templating;
        $this->securityContext = $securityContext;
        $this->session = $session;
    }

    /**
     * register twig functions
     */
    public function getFunctions()
    {
        return array(
            'cms_widget_actions' => new \Twig_Function_Method($this, 'cmsWidgetActions', array('is_safe' => array('html'))),
            'cms_slot_widgets'   => new \Twig_Function_Method($this, 'cmsSlotWidgets', array('is_safe' => array('html'))),
            'cms_slot_actions'   => new \Twig_Function_Method($this, 'cmsSlotActions', array('is_safe' => array('html'))),
            'cms_widget'         => new \Twig_Function_Method($this, 'cmsWidget', array('is_safe' => array('html'))),
            'cms_page'           => new \Twig_Function_Method($this, 'cmsPage', array('is_safe' => array('html'))),
        );
    }

    /**
     * register twig filters
     */
    public function getFilters()
    {
        return array(
            'hash' => new \Twig_Filter_Method($this, 'hash'),
        );
    }



    /**
     * get extension name
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
    public function cmsSlotWidgets(BasePage $page, $slot, $addContainer = true, $entity = null)
    {
        $result = "";
        if ($this->securityContext->isGranted('ROLE_VICTOIRE')) {
            $result .= $this->widgetManager->renderActions($slot, $page, true);
        }
        $widgets = array();

        $pageWidgets = $this->widgetManager->findByPageBySlot($page, $slot);

        foreach ($pageWidgets as $_widget) {
            //is the widget a current entity widget
            if ($_widget instanceof Widget) {//@TODO check current entity widget
                //set the entity for the widget
                $_widget->setEntity($entity);
            }

            $widgets[$_widget->getId()] = $_widget->setCurrentPage($page);
        }

        foreach ($page->getWidgetMap() as $_widgets) {
            foreach ($_widgets as $widgetId) {
                if (!empty($widgets[$widgetId])) {
                    $result .= $this->cmsWidget($widgets[$widgetId], $addContainer);
                }
            }
        }

        if ($addContainer) {
            $result = "<div class='vic-slot' id='vic-slot-".$slot."'>".$result."</div>";
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

}
