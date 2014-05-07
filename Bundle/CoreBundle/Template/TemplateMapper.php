<?php
namespace Victoire\Bundle\CoreBundle\Template;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TemplateMapper
{
    private $templating;
    private $framework;
    private $appBundle;
    private $templates;

    /**
     * construct
     *
     * @param EngineInterface      $templating Twig engine
     * @param bootstrap|foundation $framework  Templating framework used
     * @param string               $appBundle  Applicative bundle, defined in config
     * @param array                $templates  templates config
     * @return void
     **/
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->framework = $container->getParameter('victoire_core.framework');
        $this->appBundle = $container->getParameter('victoire_core.applicative_bundle');
        $this->templates = $container->getParameter('victoire_core.templates');
    }

    /**
     * Render the template
     *
     * @param string $view   The requested template key
     * @param array  $params The params to give to the template
     * @return template
     **/
    public function render($view, $params)
    {
        $template = $this->retrieveTemplate($view);

        return $this->container->get('templating')->render($template, $params);
    }

    // /**
    //  * Render the template with the current Framework
    //  *
    //  * @param string $view   The requested template key
    //  * @param array  $params The params to give to the template
    //  * @return template
    //  **/
    // public function renderByFramework($view, $params)
    // {
    //     $template = $this->retrieveTemplateByFramework($view);

    //     return $this->container->get('templating')->render($template, $params);
    // }

    /**
     * Render response with requested template
     *
     * @param string $view   The requested template key
     * @param array  $params The params to give to the template
     * @return Response
     **/
    public function renderResponse($view, $params)
    {
        $template = $this->retrieveTemplate($view);

        return $this->container->get('templating')->renderResponse($template, $params);
    }

    /**
     * Execute several strategies to retrive the template file
     *
     * @param string $view The key of requested template
     * @return Template file
     **/
    public function retrieveTemplate($view)
    {

        list($bundle, $element, $view) = array_pad(explode(":", $view), 3, null);

        if ($view) {
            $twigTemplate = $element . ":" . $view;
        } else {
            $twigTemplate = $element;
        }
        switch (true) {
            case $template = $this->getTemplate($this->appBundle, $twigTemplate):
                break;
            case $template = $this->getTemplate($bundle, $twigTemplate):
                break;
            default:
                throw new HttpException(
                    500,
                    sprintf('Requested template "%s" was not found neither in "%s" or "%s"',
                        $twigTemplate,
                        sprintf("%s:%s", $this->appBundle, $twigTemplate),
                        sprintf("%s:%s", $bundle, $twigTemplate)
                    )
                );
                break;
        }

        return $template;
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //  TODO : Change this behavior to have adaptive rendering (desktop, tablet, mobile)   //
    // instead of a framework switcher                                                     //
    /////////////////////////////////////////////////////////////////////////////////////////
    // /**
    //  * Execute several strategies to retrive the template file
    //  *
    //  * @param string $view The key of requested template
    //  * @return Template file
    //  **/
    // public function retrieveTemplateByFramework($view)
    // {

    //     list($bundle, $element, $view) = array_pad(explode(":", $view), 3, null);

    //     if ($view) {
    //         $twigTemplate = $element . "/" . $view;
    //     } else {
    //         $twigTemplate = $element;
    //     }
    //     switch (true) {
    //         case $template = $this->getTemplate($this->appBundle, $this->framework, $twigTemplate):
    //             break;
    //         case $template = $this->getTemplate($bundle, $this->framework, $twigTemplate):
    //             break;
    //         default:
    //             throw new HttpException(
    //                 500,
    //                 sprintf('Requested template "%s" was not found neither in "%s" or "%s"',
    //                     $twigTemplate,
    //                     sprintf("%s:%s:%s", $this->appBundle, $this->framework, $twigTemplate),
    //                     sprintf("%s:%s:%s", $bundle, $this->framework, $twigTemplate)
    //                 )
    //             );
    //             break;
    //     }

    //     return $template;
    // }

    public function getGlobalLayout()
    {
        if ($this->templates && array_key_exists('layout', $this->templates)) {
            return $this->templates['layout'];
        }

        return $this->retrieveTemplate("VictoireCoreBundle:layout.html.twig");
    }

    /**
     * Apply strategy to retrive template
     *
     * @param string $bundle
     * @param string $view
     * @return void
     * @author
     **/
    private function getTemplate($template)
    {
        $template = implode(':', func_get_args());
        if ($this->container->get('templating')->exists($template)) {
            return $template;
        }

        return false;
    }


}
