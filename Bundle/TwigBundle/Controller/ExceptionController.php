<?php

namespace Victoire\Bundle\TwigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Victoire\Bundle\TwigBundle\Entity\ErrorPage;

class ExceptionController extends BaseExceptionController
{
    private $em;

    public function __construct(\Twig_Environment $twig, $debug, $em, $httpKernel, $requestStack, $router)
    {
        $this->twig = $twig;
        $this->debug = $debug;
        $this->em = $em;
        $this->kernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
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
        $uriArray = explode('/', $request->getRequestUri());
        $matches = preg_match('/^.*(\..*)$/', array_pop($uriArray), $matches);

        //if in production environment and the query is not a file
        if ($this->debug === false && 0 === $matches) {
            $page = $this->em->getRepository('VictoireTwigBundle:ErrorPage')->findOneByCode($code);
            if ($page) {
                return $this->forward('VictoireTwigBundle:ErrorPage:show', [
                        'code' => $page->getCode(),
                    ]
                );
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
