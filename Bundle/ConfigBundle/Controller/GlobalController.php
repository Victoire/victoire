<?php

namespace Victoire\Bundle\ConfigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;
use Victoire\Bundle\ConfigBundle\Favicon\FaviconConfigDumper;
use Victoire\Bundle\ConfigBundle\Favicon\FaviconGenerator;
use Victoire\Bundle\ConfigBundle\Form\GlobalConfigType;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;

/**
 * Global config controller.
 *
 * @Route("/victoire-dcms/config/global")
 */
class GlobalController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * Method used to edit global config.
     *
     * @param Request             $request
     * @param FaviconConfigDumper $faviconConfigDumper
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/", name="victoire_config_global_edit")
     */
    public function editAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $globalConfig = $entityManager->getRepository(GlobalConfig::class)->find(1) ?: new GlobalConfig();
        $form = $this->createForm(GlobalConfigType::class, $globalConfig, []);
        $initialLogo = $globalConfig->getLogo();
        $form->handleRequest($request);

        if ($submited = $form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($globalConfig);
                $entityManager->flush();
                if ($initialLogo != $globalConfig->getLogo() && $globalConfig->getLogo() !== null) {
                    $this->container->get(FaviconGenerator::class)->generate(
                        $globalConfig,
                        $this->getParameter('kernel.project_dir').'/faviconConfig.json'
                    );
                }
                $this->congrat('victoire.config.global.edit.success');

                return new JsonResponse([
                    'url'     => $this->generateUrl('victoire_core_homepage_show'),
                    'success' => true,
                ]);
            }
        }

        return new JsonResponse([
            'html' => $this->renderView('VictoireConfigBundle:global:edit.html.twig', [
                'form' => $form->createView(),
            ]),
            'success' => !$submited,
        ]);
    }
}
