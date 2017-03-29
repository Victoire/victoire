<?php

namespace Victoire\Bundle\WidgetBundle\Twig;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Helper\BusinessPageHelper;
use Victoire\Bundle\CoreBundle\Entity\Link;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\TwigBundle\Entity\ErrorPage;
use Victoire\Bundle\ViewReferenceBundle\Exception\ViewReferenceNotFoundException;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * Twig extension for rendering a link.
 */
class LinkExtension extends \Twig_Extension
{
    protected $router;
    protected $analytics;
    protected $businessEntityHelper;
    protected $BusinessPageHelper;
    protected $pageHelper;
    protected $em;
    protected $errorPageRepository;
    protected $abstractBusinessTemplates;
    protected $defaultLocale;

    /**
     * LinkExtension constructor.
     *
     * @param Router               $router
     * @param RequestStack         $requestStack
     * @param string               $analytics
     * @param BusinessEntityHelper $businessEntityHelper
     * @param BusinessPageHelper   $BusinessPageHelper
     * @param PageHelper           $pageHelper
     * @param EntityManager        $em
     * @param LoggerInterface      $logger
     * @param EntityRepository     $errorPageRepository
     * @param                      $defaultLocale
     * @param array                $abstractBusinessTemplates
     */
    public function __construct(
        Router $router,
        RequestStack $requestStack,
        $analytics,
        BusinessEntityHelper $businessEntityHelper,
        BusinessPageHelper $BusinessPageHelper,
        PageHelper $pageHelper,
        EntityManager $em,
        LoggerInterface $logger,
        EntityRepository $errorPageRepository,
        $defaultLocale,
        $abstractBusinessTemplates = []
    ) {
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
        $this->analytics = $analytics;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->BusinessPageHelper = $BusinessPageHelper;
        $this->pageHelper = $pageHelper;
        $this->em = $em;
        $this->errorPageRepository = $errorPageRepository;
        $this->logger = $logger;
        $this->abstractBusinessTemplates = $abstractBusinessTemplates;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return \Twig_SimpleFunction[] An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('vic_link_url', [$this, 'victoireLinkUrl']),
            new \Twig_SimpleFunction('vic_link', [$this, 'victoireLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('vic_menu_link', [$this, 'victoireMenuLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('vic_business_link', [$this, 'victoireBusinessLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('is_vic_link_active', [$this, 'isVicLinkActive'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generate the complete URL of a link.
     *
     * @param array  $parameters   The link parameters (go to LinkTrait to have the list)
     * @param string $avoidRefresh Do we have to refresh or not ?
     * @param array  $url          Fallback url
     *
     * @return string
     */
    public function victoireLinkUrl($parameters, $avoidRefresh = true, $url = '#')
    {
        $referenceType = isset($parameters['referenceType']) ? $parameters['referenceType'] : UrlGeneratorInterface::ABSOLUTE_PATH;
        if (!isset($parameters['locale'])) {
            if ($this->request) {
                $parameters['locale'] = $this->request->getLocale();
            } else {
                $parameters['locale'] = $this->defaultLocale;
            }
        }

        $viewReference = isset($parameters['viewReference']) ? $parameters['viewReference'] : null;
        switch ($parameters['linkType']) {
            case 'viewReference':
                if ($viewReference instanceof ViewReference) {
                    $viewReference = $viewReference->getId();
                }
                $linkUrl = '';
                if (!empty($parameters['viewReferencePage'])) {
                    $page = $parameters['viewReferencePage'];
                } else {
                    $params = [
                        'id'     => $viewReference,
                        'locale' => $parameters['locale'],
                    ];
                    try {
                        $page = $this->pageHelper->findPageByParameters($params);
                    } catch (ViewReferenceNotFoundException $e) {
                        $this->logger->error($e->getMessage(), $params);
                        /** @var ErrorPage $page */
                        $page = $this->errorPageRepository->findOneByCode(404);
                        $linkUrl = $this->router->generate(
                            'victoire_core_page_show', array_merge([
                            '_locale' => $parameters['locale'],
                            'url'     => $page->getSlug(),
                        ], $params));
                    }
                }

                if ($page instanceof WebViewInterface) {
                    $linkUrl = $this->router->generate(
                        'victoire_core_page_show', [
                            '_locale' => $parameters['locale'],
                            'url'     => $page->getReference($parameters['locale'])->getUrl(),
                        ],
                        $referenceType
                    );
                }

                if (!$this->request || ($this->request && $this->request->getRequestUri() !== $linkUrl) || !$avoidRefresh) {
                    $url = $linkUrl;
                }
                break;
            case 'route':
                $url = $this->router->generate($parameters['route'], $parameters['routeParameters'], $referenceType);
                break;
            case Link::TYPE_WIDGET:
                $attachedWidget = $parameters[Link::TYPE_WIDGET];
                $url = '';

                //If Widget's View has an url and Widget is not in the current View, add this url in the link
                if ($attachedWidget && method_exists($attachedWidget->getWidgetMap()->getView(), 'getUrl')
                    && (!$this->request || rtrim($this->request->getRequestUri(), '/') != rtrim($url, '/'))
                ) {
                    /** @var View $view */
                    $view = $attachedWidget->getWidgetMap()->getView();
                    /* @var Widget $attachedWidget */
                    $locale = $attachedWidget->getLocale($this->request ? $this->request->getLocale() : $this->defaultLocale);
                    $view->translate($locale);
                    $url .= $this->router->generate('victoire_core_page_show', ['_locale' => $locale, 'url' => $view->getUrl()], $referenceType);
                }

                //Add anchor part
                $url .= '#widget-'.$attachedWidget->getId();
                break;
            default:
                $url = $parameters['url'];
        }

        return $url;
    }

    /**
     * Generate the complete link (with the a tag).
     *
     * @param array  $parameters The link parameters (go to LinkTrait to have the list)
     * @param string $label      link label
     * @param array  $attr       custom attributes
     *
     * @return string
     */
    public function victoireLink($parameters, $label, $attr = [], $currentClass = 'active', $url = '#')
    {
        $referenceLink = UrlGeneratorInterface::ABSOLUTE_PATH;
        /** @var Widget $attachedWidget */
        $attachedWidget = isset($parameters[Link::TYPE_WIDGET]) ? $parameters[Link::TYPE_WIDGET] : null;

        if ($parameters['linkType'] == Link::TYPE_WIDGET && $attachedWidget && method_exists($attachedWidget->getWidgetMap()->getView(), 'getUrl')) {
            $viewUrl = $this->router->generate('victoire_core_page_show', ['_locale' => $attachedWidget->getWidgetMap()->getView()->getCurrentLocale(), 'url' => $attachedWidget->getWidgetMap()->getView()->getUrl()], $referenceLink);
            if ($this->request && (rtrim($this->request->getRequestUri(), '/') == rtrim($viewUrl, '/'))) {
                $attr['data-scroll'] = 'smooth';
            }
        }

        //Avoid to refresh page if not needed
        if ($this->request && ($this->request->getRequestUri() == $this->victoireLinkUrl($parameters, false))) {
            $this->addAttr('class', $currentClass, $attr);
        }

        //Build the target attribute
        if ($parameters['target'] == Link::TARGET_MODAL) {
            $attr['data-toggle'] = 'viclink-modal';
        } elseif ($parameters['target'] == '') {
            $attr['target'] = '_parent';
        } else {
            $attr['target'] = $parameters['target'];
        }

        //Add the analytics tracking code attribute
        if (isset($parameters['analyticsTrackCode'])) {
            $this->addAttr('onclick', $parameters['analyticsTrackCode'], $attr);
        }

        $url = $this->victoireLinkUrl($parameters, true, $url);
        // if modalLayout is set, we add it as GET parameter
        if ($parameters['target'] == Link::TARGET_MODAL && !empty($parameters['modalLayout'])) {
            $url .= !preg_match('/\?/', $url) ? '?' : '&';
            $url .= 'modalLayout='.$parameters['modalLayout'];
        }
        //Creates a new twig environment
        $twig = new \Twig_Environment(new \Twig_Loader_Array(['linkTemplate' => '{{ link|raw }}']));

        return $twig->render('linkTemplate', ['link' => '<a href="'.$url.'" '.$this->formatAttributes($attr).'>'.$label.'</a>']);
    }

    /**
     * Generate the complete menu link item (with the li tag).
     *
     * @param array  $parameters The link parameters (go to LinkTrait to have the list)
     * @param string $label      link label
     * @param array  $attr       custom attributes
     *
     * @return string
     */
    public function victoireMenuLink($parameters, $label, $linkAttr = [], $listAttr = [])
    {
        if ($this->request && ($this->request->getRequestUri() == $this->victoireLinkUrl($parameters, false))) {
            $this->addAttr('class', 'active', $listAttr);
        }

        return '<li '.$this->formatAttributes($listAttr).'>'.$this->victoireLink($parameters, $label, $linkAttr, false, '#top').'</li>';
    }

    public function victoireBusinessLink($businessEntityInstance, $templateId = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (isset($this->abstractBusinessTemplates[$templateId])) {
            $templateId = $this->abstractBusinessTemplates[$templateId];
        }
        if (!$templateId) {
            $templateId = $this->BusinessPageHelper
                ->guessBestPatternIdForEntity($businessEntityInstance, $this->em);
        }

        $page = $this->pageHelper->findPageByParameters([
            'templateId' => $templateId,
            'entityId'   => $businessEntityInstance->getId(),
            'locale'     => $this->request ? $this->request->getLocale() : $this->defaultLocale,
        ]);

        $parameters = [
            'linkType'        => 'route',
            'route'           => 'victoire_core_page_show',
            'routeParameters' => [
                'url'     => $page->getReference()->getUrl(),
                '_locale' => $page->getCurrentLocale(),
            ],
            'referenceType' => $referenceType,
        ];

        return $this->victoireLinkUrl($parameters);
    }

    /**
     * Check if a given Link is active for current request.
     *
     * @param Link $link
     *
     * @return bool
     */
    public function isVicLinkActive(Link $link)
    {
        return $this->request && ($this->request->getRequestUri() == $this->victoireLinkUrl($link->getParameters(), false));
    }

    /**
     * Add a given attribute to given attributes.
     *
     * @param string $label
     * @param string $value
     * @param array  $attr  The current attributes array
     *
     * @return LinkExtension
     **/
    protected function addAttr($label, $value, &$attr)
    {
        if (!isset($attr[$label])) {
            $attr[$label] = '';
        } else {
            $attr[$label] .= ' ';
        }
        $attr[$label] .= $value;

        return $this;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'victoire_link_extension';
    }

    private function formatAttributes($attributes)
    {
        array_walk($attributes, function (&$item, $key) {
            if (is_array($item)) {
                $item = implode($item, ' ');
            }
            $item = $key.'="'.$item.'"';
        });

        return implode($attributes, ' ');
    }
}
