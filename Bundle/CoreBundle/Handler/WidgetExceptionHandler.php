<?php

namespace Victoire\Bundle\CoreBundle\Handler;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * ref: victoire_core.widget_exception_handler.
 */
class WidgetExceptionHandler
{
    protected $authorizationChecker;
    protected $debug;
    protected $twig;
    protected $templating;

    /**
     * Constructor.
     *
     * @param SecurityContext  $authorizationChecker
     * @param TwigEngine       $twig
     * @param bool             $debug                The debug variable environment
     * @param EngineInterface  $templating
     */
    public function __construct(AuthorizationChecker $authorizationChecker, $twig, $debug, EngineInterface $templating)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->twig = $twig;
        $this->debug = $debug;
        $this->templating = $templating;
    }

    /**
     * Handle response for an exception for a widget.
     *
     * @param View   $currentView
     * @param Widget $widget
     * @param int    $widgetId
     *
     * @return string The html with the Error
     */
    public function handle(\Exception $ex, $currentView, $widget = null, $widgetId = null)
    {
        $result = '';

        //
        if ($this->debug) {
            $exceptionResult = '<div style="border: 3px solid #FF0000;height: 500px;overflow: auto;">';

            $template = new TemplateReference('TwigBundle', 'Exception', 'exception', 'html', 'twig');
            $exception = FlattenException::create($ex);
            $exceptionResult = $this->twig->render(
                $template,
                [
                    'status_code'    => $ex->getCode(),
                    'status_text'    => 500,
                    'exception'      => $exception,
                    'logger'         => null,
                    'currentContent' => null,
                ]
            );

            $exceptionResult .= '</div>';

            //only a user victoire can see that there is an error
            $result = $this->templating->render(
                'VictoireCoreBundle:Widget:showError.html.twig',
                [
                    'widget'      => $widget,
                    'widgetId'    => $widgetId,
                    'currentView' => $currentView,
                    'error'       => $exceptionResult,
                ]
            );
        } else {
            //environnement not debug
            //only a user victoire can see that there is an error
            if ($this->authorizationChecker->isGranted('ROLE_VICTOIRE')) {
                $result = $this->templating->render(
                    'VictoireCoreBundle:Widget:showError.html.twig',
                    [
                        'widget'      => $widget,
                        'widgetId'    => $widgetId,
                        'currentView' => $currentView,
                        'error'       => $ex->getMessage(),
                    ]
                );
            }
        }

        return $result;
    }
}
