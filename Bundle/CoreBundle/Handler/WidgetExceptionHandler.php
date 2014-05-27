<?php
namespace Victoire\Bundle\CoreBundle\Handler;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\TwigBundle\TwigEngine;

class WidgetExceptionHandler
{
    private $security;
    private $debug;
    private $templating;

    public function __construct(SecurityContext $security, TwigEngine $templating, $debug)
    {
        $this->security = $security;
        $this->templating = $templating;
        $this->debug = $debug;
    }

    public function handle(\Exception $exception, $widget)
    {
        if ($this->debug === true) {
            throw $exception;
        } elseif ($this->security->isGranted('ROLE_VICTOIRE')) {
            return $this->templating->render(
                'VictoireCoreBundle:Widget:showError.html.twig',
                array(
                    "widget" => $widget,
                    "error" => $exception->getMessage(),
                )
            );
        } else {
            return '';
        }
    }
}
