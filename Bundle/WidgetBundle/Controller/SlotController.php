<?php

namespace Victoire\Bundle\WidgetBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Slot Controller
 *
 */
class SlotController extends Controller
{
    /**
     * Get the new content button for the given slot and options
     *
     * @param string  $slot    The slot where attach the widget
     * @param array   $options The slot options
     *
     * @return JsonResponse
     *
     * @Route("/victoire-dcms/slot/newContentButton/{slotId}/{options}", name="victoire_core_slot_newContentButton", options={"expose"=true})
     * @Template()
     */
    public function newContentButtonAction($slotId, $options)
    {
        $html = $this->get('victoire_widget.widget_renderer')->renderActions($slotId, $options);

        return new JsonResponse(array(
                'html' => $html
            ));
    }
}
