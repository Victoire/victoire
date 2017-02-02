<?php

namespace Victoire\Bundle\TwigBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Routing\Router;

/**
 * Redirects to a victoire error page when facing a Flatted Exception.
 */
class ExceptionController extends BaseExceptionController
{
    private $em;

    /**
     * @var array Available locales for the Victoire website
     */
    private $availableLocales;
    /**
     * @var string the fallback locale
     */
    private $defaultLocale;

    /**
     * ExceptionController constructor.
     *
     * @param \Twig_Environment   $twig
     * @param bool                $debug
     * @param EntityManager       $em
     * @param HttpKernelInterface $httpKernel
     * @param RequestStack        $requestStack
     * @param Router              $router
     * @param array               $availableLocales
     * @param string              $defaultLocale
     */
    public function __construct(\Twig_Environment $twig, $debug, EntityManager $em, HttpKernelInterface $httpKernel, RequestStack $requestStack, Router $router, array $availableLocales, $defaultLocale)
    {
        parent::__construct($twig, $debug);
        $this->twig = $twig;
        $this->debug = $debug;
        $this->em = $em;
        $this->kernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->availableLocales = $availableLocales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Converts an Exception to a Response to be able to render a Victoire view.
     *
     * @param Request              $request   The request
     * @param FlattenException     $exception A FlattenException instance
     * @param DebugLoggerInterface $logger    A DebugLoggerInterface instance
     * @param string               $_format   The format to use for rendering (html, xml, ...)
     *
     * @throws \InvalidArgumentException When the exception template does not exist
     *
     * @return Response
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null, $_format = 'html')
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));
        $code = $exception->getStatusCode();

        //get request extension
        $uriArray = explode('/', rtrim($request->getRequestUri(), '/'));

        $matches = preg_match('/^.*(\..*)$/', array_pop($uriArray), $matches);

        $locale = $request->getLocale();
        if (!in_array($locale, $this->availableLocales, true)) {
            $locale = $this->defaultLocale;
        }
        //if in production environment and the query is not a file
        if ($this->debug === false && 0 === $matches) {
            $page = $this->em->getRepository('VictoireTwigBundle:ErrorPage')->findOneByCode($code);
            if ($page) {
                return $this->forward('VictoireTwigBundle:ErrorPage:show', [
                        'code'    => $page->getCode(),
                        '_locale' => $locale,
                ]);
            }
        }

        return new Response($this->twig->render(
            $this->findTemplate($request, $_format, $code, $this->debug),
            [
                'status_code'    => $code,
                'status_text'    => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                'exception'      => $exception,
                'logger'         => $logger,
                'currentContent' => $currentContent,
            ]
        ));
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like BlogBundle:Post:index)
     * @param array  $path       An array of path parameters
     * @param array  $query      An array of query parameters
     *
     * @return Response A Response instance
     */
    protected function forward($controller, array $path = [], array $query = [])
    {
        $path['_controller'] = $controller;
        $subRequest = $this->request->duplicate($query, null, $path);

        return $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
