<?php

namespace Victoire\Bundle\WidgetBundle\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;

/**
 * Widget Cache Controller.
 */
class WidgetCacheController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * Clear the widgetcache.
     *
     * @param Request $request
     *
     * @Route("/victoire-dcms-public/widget-cache/clear", name="victoire_core_widget_cache_clear")
     *
     * @throws Exception
     *
     * @return Response
     */
    public function clearAction(Request $request)
    {
        if (!$this->getParameter('kernel.debug')) {
            throw new AccessDeniedException('You should be in debug mode to access this feature');
        }
        $this->get('victoire_widget.widget_cache')->clear();

        return $this->redirect($request->headers->get('referer'));
    }
}
