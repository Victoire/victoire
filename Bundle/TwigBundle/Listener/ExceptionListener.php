<?php

namespace Victoire\Bundle\TwigBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Core\Exception\LogoutException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * ExceptionListener catches authentication exception and converts them to
 * Response instances.
 *
 * @author Leny BERNARD <leny@appventus.com>
 */
class ExceptionListener extends BaseExceptionListener
{
    private $context;
    private $providerKey;
    private $accessDeniedHandler;
    private $authenticationEntryPoint;
    private $authenticationTrustResolver;
    private $errorPage;
    private $logger;
    private $httpUtils;

    public function __construct(SecurityContextInterface $context, AuthenticationTrustResolverInterface $trustResolver, HttpUtils $httpUtils, $providerKey, AuthenticationEntryPointInterface $authenticationEntryPoint = null, $errorPage = null, AccessDeniedHandlerInterface $accessDeniedHandler = null, LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->accessDeniedHandler = $accessDeniedHandler;
        $this->httpUtils = $httpUtils;
        $this->providerKey = $providerKey;
        $this->authenticationEntryPoint = $authenticationEntryPoint;
        $this->authenticationTrustResolver = $trustResolver;
        $this->errorPage = $errorPage;
        $this->logger = $logger;
    }

    /**
     * Handles security related exceptions.
     *
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        do {
            if ($exception instanceof AuthenticationException) {
                return $this->handleAuthenticationException($event, $exception);
            } elseif ($exception instanceof AccessDeniedException) {
                return $this->handleAccessDeniedException($event, $exception);
            } elseif ($exception instanceof LogoutException) {
                return $this->handleLogoutException($event, $exception);
            } elseif ($exception instanceof NotFoundHttpException) {
                return $this->handleNotFoundException($event, $exception);
            }
        } while (null !== $exception = $exception->getPrevious());
    }

    private function handleNotFoundException(GetResponseForExceptionEvent $event, NotFoundHttpException $exception)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Not Found exception occured (%s)', $exception->getMessage()));
        }
    }

    private function handleAuthenticationException(GetResponseForExceptionEvent $event, AuthenticationException $exception)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Authentication exception occurred; redirecting to authentication entry point (%s)', $exception->getMessage()));
        }

        try {
            $event->setResponse($this->startAuthentication($event->getRequest(), $exception));
        } catch (\Exception $e) {
            $event->setException($e);
        }
    }

    private function handleAccessDeniedException(GetResponseForExceptionEvent $event, AccessDeniedException $exception)
    {
        $event->setException(new AccessDeniedHttpException($exception->getMessage(), $exception));

        $token = $this->context->getToken();
        if (!$this->authenticationTrustResolver->isFullFledged($token)) {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Access is denied (user is not fully authenticated) by "%s" at line %s; redirecting to authentication entry point', $exception->getFile(), $exception->getLine()));
            }

            try {
                $insufficientAuthenticationException = new InsufficientAuthenticationException('Full authentication is required to access this resource.', 0, $exception);
                $insufficientAuthenticationException->setToken($token);

                $event->setResponse($this->startAuthentication($event->getRequest(), $insufficientAuthenticationException));
            } catch (\Exception $e) {
                $event->setException($e);
            }

            return;
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Access is denied (and user is neither anonymous, nor remember-me) by "%s" at line %s', $exception->getFile(), $exception->getLine()));
        }

        try {
            if (null !== $this->accessDeniedHandler) {
                $response = $this->accessDeniedHandler->handle($event->getRequest(), $exception);

                if ($response instanceof Response) {
                    $event->setResponse($response);
                }
            } elseif (null !== $this->errorPage) {
                $subRequest = $this->httpUtils->createRequest($event->getRequest(), $this->errorPage);
                $subRequest->attributes->set(SecurityContextInterface::ACCESS_DENIED_ERROR, $exception);

                $event->setResponse($event->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST, true));
            }
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage()));
            }

            $event->setException(new \RuntimeException('Exception thrown when handling an exception.', 0, $e));
        }
    }

    private function handleLogoutException(GetResponseForExceptionEvent $event, LogoutException $exception)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Logout exception occurred; wrapping with AccessDeniedHttpException (%s)', $exception->getMessage()));
        }
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $authException
     *
     * @return Response
     * @throws AuthenticationException
     */
    private function startAuthentication(Request $request, AuthenticationException $authException)
    {
        if (null === $this->authenticationEntryPoint) {
            throw $authException;
        }

        if (null !== $this->logger) {
            $this->logger->debug('Calling Authentication entry point');
        }

        $this->setTargetPath($request);

        if ($authException instanceof AccountStatusException) {
            // remove the security token to prevent infinite redirect loops
            $this->context->setToken(null);
        }

        return $this->authenticationEntryPoint->start($request, $authException);
    }

    /**
     * @param Request $request
     */
    protected function setTargetPath(Request $request)
    {
        // session isn't required when using HTTP basic authentication mechanism for example
        if ($request->hasSession() && $request->isMethodSafe()) {
            $request->getSession()->set('_security.'.$this->providerKey.'.target_path', $request->getUri());
        }
    }
}
