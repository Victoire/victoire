<?php

namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * This class add items in admin menu.
 **/
class MenuDispatcher
{
    protected $eventDispatcher;

    /**
     * Construct function to include eventDispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface    $tokenStorage
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Dispatch event to build the Victoire's global menu items.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->tokenStorage->getToken() && $this->authorizationChecker->isGranted('ROLE_VICTOIRE')) {
            $this->eventDispatcher->dispatch('victoire_core.build_menu', $event);
        }
    }
}
