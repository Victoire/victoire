<?php
namespace Victoire\Bundle\I18nBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class I18nController extends Controller
{

    /**
     * Change user locale
     * @param Request $request
     * @param $targetLocale string 
     *
     * @Route("/switchLocale/{targetLocale}", name="victoire_i18n_switch_locale")
     *
     * @return RedirectResponse
     */
    public function switchLocaleAction(Request $request,  $targetLocale)
    {
    	$request->getSession()->set('victoire_locale', $targetLocale);
    	$referer = $request->request->get('referer', $request->headers->get('referer'));

        return new RedirectResponse($referer);
    }

    /**
     * Render switch locale bar
     * @param Request $request
     *
     * @Route("/getLocaleSwitcher", name="victoire_i18n_get_locale_switcher")
     *
     * @return Response
     */
    public function getLocaleSwitcherAction(Request $request)
    {
    	$locales = $this->container->getParameter('application_locales');
        
        return $this->render(
    		'VictoireI18nBundle:I18n:locale_switcher.html.twig',
    		array('locales' => $locales)
		);
    }

}
