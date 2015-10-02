<?php

namespace Victoire\Bundle\CoreBundle\Twig\Extension;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Victoire\Bundle\PageBundle\Helper\PageHelper;

/**
 * class RoutingExtension.
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

    public function getPath($name, $parameters = [], $relative = false)
    {
        if ($name == 'victoire_core_page_show_by_id') {
            $params = ['viewId' => $parameters['viewId']];
            unset($parameters['viewId']);
            if (!empty($parameters['entityId'])) {
                $params['entityId'] = $parameters['entityId'];
                unset($parameters['entityId']);
            }
            $page = $this->pageHelper->findPageByParameters($params);
            $parameters['url'] = $page->getUrl();

            $name = 'victoire_core_page_show';
        }

        return $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::ABSOLUTE_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
