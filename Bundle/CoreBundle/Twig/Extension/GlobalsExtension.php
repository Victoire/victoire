<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Victoire\Bundle\CoreBundle\Menu\MenuManager;
use Victoire\Bundle\CoreBundle\Widget\Managers\WidgetManager;
use Victoire\Bundle\CoreBundle\Template\TemplateMapper;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * Provides some gloval variabls to twig
 *
 */
class GlobalsExtension extends \Twig_Extension
{
    protected $templateMapper;
    protected $session;

    /**
     * contructor
     */
    public function __construct(TemplateMapper $templateMapper, $session)
    {
        $this->templateMapper = $templateMapper;
        $this->session = $session;
    }

    public function getGlobals()
    {
        return array(
            "global_layout" => $this->templateMapper->getGlobalLayout(),
            "edit_mode" => $this->session->get('victoire.edit_mode', false),
        );
    }

    public function getName()
    {
        return 'Globals_extention';
    }

}
