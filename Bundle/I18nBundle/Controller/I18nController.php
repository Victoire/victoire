<?php

namespace Victoire\Bundle\I18nBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class I18nController extends Controller
{
	/**
     * @param int $pageId  The id of the original page
     *
     * @Route("/addTranslation/{pageId}", requirements={"pageId" = "\d+"}, name="victoire_i18n_page_translation")
     * @return JsonResponse
     */
    public function addTranslationAction($pageId)
    {
        return $this->render('VictoireI18nBundle:i18n:addtranslation.html.twig', array('pageId' => $pageId));
    }
}
