<?php
namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

/**
 * class RoutingExtension
 */
class RoutingExtention extends RoutingExtension
{

    private $pageHelper;
    private $generator;

    public function __construct(PageHelper $pageHelper, UrlGeneratorInterface $generator)
    {
        $this->pageHelper = $pageHelper;
        $this->generator = $generator;
        parent::__construct($generator);
    }

    public function getPath($name, $parameters = array(), $relative = false)
    {
        if ($name == 'victoire_core_page_show_by_id') {
            $viewId = $parameters['viewId'];
            $entityId = $parameters['entityId'];

            $page = $this->pageHelper->findPageByParameters(array('viewId' => $viewId, 'entityId' => $entityId));

            return $this->generator->generate('victoire_core_page_show', array('url' => $page->getUrl()));
        }

        foreach ($parameters as $_key => $_value) {
            //if the value contains a curly bracket, it means that there is no entity to render with
            if (is_string($_value) && strstr($_value, '{') != null) {
                return $this->generator->generate('vic_widget_render_getStaticContent', array('path' => $name), $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
            }
        }

        return $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
