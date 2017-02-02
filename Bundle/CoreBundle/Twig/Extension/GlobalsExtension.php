<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Twig_Extension_GlobalsInterface;

/**
 * Provides some gloval variabls to twig.
 */
class GlobalsExtension extends \Twig_Extension implements Twig_Extension_GlobalsInterface
{
    protected $session;

    /**
     * contructor.
     *
     * @param unknown $session
     */
    public function __construct($session)
    {
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
            'edit_mode' => $this->session->get('victoire.edit_mode', false),
        ];
    }

    /**
     * The name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'Globals_extension';
    }
}
