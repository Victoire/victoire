<?php
namespace Victoire\Bundle\CoreBundle\Handler;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Debug\Exception\FlattenException;
use Victoire\Bundle\PageBundle\Entity\Template;

/**
 * ref: victoire_core.widget_exception_handler
 */
class WidgetExceptionHandler
{
    protected $security;
    protected $debug;
    protected $twig;
    protected $templating;

    /**
     * Constructor
     *
     * @param SecurityContext $security
     * @param TwigEngine      $templating
     * @param boolean         $debug      The debug variable environment
     * @param Template        $templating The victoire templating
     */
    public function __construct(SecurityContext $security,  $twig, $debug, $templating)
    {
        $this->security = $security;
        $this->twig = $twig;
        $this->debug = $debug;
        $this->templating = $templating;
    }

    /**
     * Handle response for an exception for a widget
     *
     * @param \Exception $exception
     * @param Widget     $widget    The widget that throwed an error
     *
     * @return string The html with the Error
     */
    public function handle(\Exception $ex, $widget = null)
    {
        $result = '';

        //
        if ($this->debug) {
            $exceptionResult = '<div style="border: 3px solid #FF0000;height: 500px;overflow: auto;">';

            $template = new TemplateReference('TwigBundle', 'Exception', 'exception', 'html', 'twig');
            $exception = FlattenException::create($ex);
            $exceptionResult = $this->twig->render(
                $template,
                array(
                    'status_code'    => $ex->getCode(),
                    'status_text'    => 500,
                    'exception'      => $exception,
                    'logger'         => null,
                    'currentContent' => null,
                )
            );

            $exceptionResult .= '</div>';

            //only a user victoire can see that there is an error
            $result = $this->templating->render(
                'VictoireCoreBundle:Widget:showError.html.twig',
                array(
                    "widget" => $widget,
                    "error" => $exceptionResult
                )
            );
        } else {
            //environnement not debug
            //only a user victoire can see that there is an error
            if ($this->security->isGranted('ROLE_VICTOIRE')) {
                $result = $this->templating->render(
                    'VictoireCoreBundle:Widget:showError.html.twig',
                    array(
                        "widget" => $widget,
                        "error" => $ex->getMessage()
                    )
                );
            }
        }

        return $result;
    }
}
