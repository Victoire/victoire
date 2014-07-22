<?php
namespace Victoire\Bundle\CoreBundle\Widget\Managers;

/**
 *
 * @author Paul Andrieux
 *
 */
interface ManagerInterface
{
    /**
     * constructor
     *
     * @param ServiceContainer $container
     */
    public function __construct($container);
    /**
     * create a new ThemeRedactorNewspaper
     * @param Page   $page
     * @param string $slot
     *
     * @return $widget
     */
    public function newWidget($page, $slot);

    /**
     * render the ThemeRedactorNewspaper
     * @param Widget $widget
     *
     * @return widget show
     */
    public function render($widget);

    /**
     * render ThemeRedactorNewspaper form
     * @param Form                   $form
     * @param ThemeRedactorNewspaper $widget
     * @param BusinessEntity         $entity
     * @return form
     */
    public function renderForm($form, $widget, $entity = null);

    /**
     * create a form with given widget
     * @param ThemeRedactorNewspaper $widget
     * @param string                 $entityName
     * @param string                 $namespace
     * @return $form
     */
    public function buildForm($widget, $entityName = null, $namespace = null);

    /**
     * create form new for ThemeRedactorNewspaper
     * @param Form                   $form
     * @param ThemeRedactorNewspaper $widget
     * @param string                 $slot
     * @param Page                   $page
     * @param string                 $entity
     *
     * @return new form
     */
    public function renderNewForm($form, $widget, $slot, $page, $entity = null);
}
