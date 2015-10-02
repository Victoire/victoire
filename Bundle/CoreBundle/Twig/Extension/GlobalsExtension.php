<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Victoire\Bundle\CoreBundle\Template\TemplateMapper;

/**
 * Provides some gloval variabls to twig.
 */
class GlobalsExtension extends \Twig_Extension
{
    protected $templateMapper;
    protected $session;

    /**
     * contructor.
     *
     * @param TemplateMapper $templateMapper
     * @param unknown        $session
     */
    public function __construct(TemplateMapper $templateMapper, $session)
    {
        $this->templateMapper = $templateMapper;
        $this->session = $session;
    }

    /**
     * Get the globals.
     *
     * @return array
     */
    public function getGlobals()
    {
        return [
            'global_layout' => $this->templateMapper->getGlobalLayout(),
            'edit_mode'     => $this->session->get('victoire.edit_mode', false),
        ];
    }

    /**
     * The name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'Globals_extention';
    }
}
