<?php

namespace Victoire\Bundle\ConfigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\ConfigBundle\Entity\GlobalConfig;
use Victoire\Bundle\ConfigBundle\Favicon\FaviconGenerator;
use Victoire\Bundle\ConfigBundle\Form\GlobalConfigType;
use Victoire\Bundle\ConfigBundle\Repository\GlobalConfigRepository;
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
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="victoire_config_global_edit")
     */
    public function editAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        /** @var GlobalConfigRepository $globalConfigRepository */
        $globalConfigRepository = $entityManager->getRepository(GlobalConfig::class);
        $globalConfig = $globalConfigRepository->findLast() ?: new GlobalConfig();
        $form = $this->createForm(GlobalConfigType::class, $globalConfig, []);
        $initialLogo = $globalConfig->getLogo();
        $form->handleRequest($request);

        $success = true;
        if ($form->isSubmitted()) {
            if (true === $success = $form->isValid()) {
                //cloning the entity will give it a new id in order to invalidate browser cache (in meta.html.twig)
                $entityManager->clear(GlobalConfig::class);
                $entityManager->persist($globalConfig);
                $entityManager->flush();
                if ($initialLogo !== $globalConfig->getLogo() && $globalConfig->getLogo() !== null) {
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
            'success' => $success,
        ]);
    }
}
