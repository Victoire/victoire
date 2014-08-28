<?php
namespace Victoire\Bundle\CoreBundle\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * This class add items in admin menu
 *
 *
 **/
class MenuDispatcher
{
    protected $eventDispatcher;

    /**
     * Construct function to include eventDispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param SecurityContext          $securityContext
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, SecurityContext $securityContext)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->securityContext = $securityContext;

    }

    /**
     * Dispatch event to build the Victoire's global menu items
     *
     * @param GetResponseEvent $event
     *
     * @SuppressWarnings checkUnusedFunctionParameters
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->securityContext->getToken() && $this->securityContext->isGranted('ROLE_VICTOIRE')) {
            $this->eventDispatcher->dispatch('victoire_core.build_menu', $event);
        }
    }
}
