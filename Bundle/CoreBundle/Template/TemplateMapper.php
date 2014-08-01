<?php
namespace Victoire\Bundle\CoreBundle\Template;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The template mapper
 * ref: victoire_templating
 */
class TemplateMapper
{
    protected $container;
    protected $framework;
    protected $appBundle;
    protected $templates;

    /**
     * constructor
     *
     * @param ContainerInterface $container
     */
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
     *
     * @return template
     **/
    public function render($view, $params)
    {
        $template = $this->retrieveTemplate($view);

        return $this->container->get('templating')->render($template, $params);
    }

    /**
     * Render response with requested template
     *
     * @param string $view   The requested template key
     * @param array  $params The params to give to the template
     *
     * @return Response
     **/
    public function renderResponse($view, $params)
    {
        //the template
        $template = $this->retrieveTemplate($view);

        //the templating
        $templating = $this->container->get('templating');

        return $templating->renderResponse($template, $params);
    }

    /**
     * Execute several strategies to retrive the template file
     *
     * @param string $view The key of requested template
     *
     * @return Template file
     *
     * @throws HttpException
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

    /**
     * Get the global layout
     *
     * @return Ambigous <\Victoire\Bundle\CoreBundle\Template\Template, void, boolean, string>
     */
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
     * @param string $template
     *
     * @return string|boolean
     */
    protected function getTemplate($template)
    {
        $template = implode(':', func_get_args());
        if ($this->container->get('templating')->exists($template)) {
            return $template;
        }

        return false;
    }
}
