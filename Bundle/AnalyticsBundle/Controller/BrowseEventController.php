<?php

namespace Victoire\Bundle\AnalyticsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\AnalyticsBundle\Entity\BrowseEvent;
use Victoire\Bundle\UserBundle\Entity\User;

/**
 * @Route("/browseEvent")
 */
class BrowseEventController extends Controller
{
    /**
     * @Route("/track/{viewReferenceId}/{referer}", name="victoire_analytics_track")
     */
    public function trackAction(Request $request, $viewReferenceId, $referer = null)
    {
        $browseEvent = new BrowseEvent();
        $browseEvent->setViewReferenceId($viewReferenceId);
        $browseEvent->setIp($request->getClientIp());
        $browseEvent->setReferer($referer);
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $browseEvent->setAuthor($this->getUser());
        }
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entityManager->persist($browseEvent);
        $entityManager->flush();

        return new Response();
    }

    /**
     * @Route("/heartbeat/{id}", name="victoire_analytics_heartbeat")
     */
    public function heartbeatAction($id)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $entityManager->getRepository($this->getParameter('victoire_core.user_class'))->find($id);
        $user->setHeartbeat(new \DateTime());
        $entityManager->flush();
        $this->get('logger')->info('victoire.analytics.user_heartbeat');

        return new Response(null, 202);
    }
}
