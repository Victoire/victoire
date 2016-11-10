<?php

namespace Victoire\Bundle\PageBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\ViewReferenceBundle\ViewReference\ViewReference;

/**
 * The base page controller is used to interact with all kind of pages.
 **/
class BasePageController extends Controller
{
    use VictoireAlertifyControllerTrait;

    public function showAction(Request $request, $url = '')
    {
        $response = $this->get('victoire_page.page_helper')->renderPageByUrl(
            $url,
            $request->getLocale(),
            $request->isXmlHttpRequest() ? $request->query->get('modalLayout', null) : null
        );

        //throw an exception is the page is not valid
        return $response;
    }

    public function showByIdAction(Request $request, $viewId, $entityId = null)
    {
        $parameters = [
            'viewId' => $viewId,
            'locale' => $request->getLocale(),
        ];
        if ($entityId) {
            $parameters['entityId'] = $entityId;
        }
        $page = $this->get('victoire_page.page_helper')->findPageByParameters($parameters);

        return $this->redirect($this->generateUrl('victoire_core_page_show', array_merge(
                ['url' => $page->getReference()->getUrl()],
                $request->query->all()
            )
        ));
    }

    public function showBusinessPageByIdAction(Request $request, $entityId, $type)
    {
        $businessEntityHelper = $this->get('victoire_core.helper.queriable_business_entity_helper');
        $businessEntity = $businessEntityHelper->findById($type);
        $entity = $businessEntityHelper->getByBusinessEntityAndId($businessEntity, $entityId);

        $refClass = new \ReflectionClass($entity);

        $templateId = $this->get('victoire_business_page.business_page_helper')
            ->guessBestPatternIdForEntity($refClass, $entityId, $this->container->get('doctrine.orm.entity_manager'));

        $page = $this->get('victoire_page.page_helper')->findPageByParameters([
            'viewId'   => $templateId,
            'entityId' => $entityId,
            'locale'   => $request->getLocale(),
        ]);
        $this->get('victoire_widget_map.builder')->build($page);
        $this->get('victoire_widget_map.widget_data_warmer')->warm(
            $this->get('doctrine.orm.entity_manager'),
            $page
        );

        return $this->redirect(
            $this->generateUrl(
                'victoire_core_page_show',
                [
                    'url' => $page->getReference()->getUrl(),
                ]
            )
        );
    }

    /**
     * New page.
     *
     * @param bool $isHomepage
     *
     * @return []
     */
    protected function newAction(Request $request, $isHomepage = false)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $page = $this->getNewPage();
        if ($page instanceof Page) {
            $page->setHomepage($isHomepage ? $isHomepage : 0);
        }

        $form = $this->get('form.factory')->create($this->getNewPageType(), $page);

        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            if ($page->getParent()) {
                $pageNb = count($page->getParent()->getChildren());
            } else {
                $pageNb = count($entityManager->getRepository('VictoirePageBundle:BasePage')->findByParent(null));
            }
            // + 1 because position start at 1, not 0
            $page->setPosition($pageNb + 1);

            $page->setAuthor($this->getUser());
            $entityManager->persist($page);
            $entityManager->flush();

            // If the $page is a BusinessEntity (eg. an Article), compute it's url
            if (null !== $this->get('victoire_core.helper.business_entity_helper')->findByEntityInstance($page)) {
                $page = $this
                        ->get('victoire_business_page.business_page_builder')
                        ->generateEntityPageFromTemplate($page->getTemplate(), $page, $entityManager);
            }

            $this->congrat($this->get('translator')->trans('victoire_page.create.success', [], 'victoire'));
            $viewReference = $this->get('victoire_view_reference.repository')->getOneReferenceByParameters([
                'viewId' => $page->getId(),
                'locale' => $request->getLocale(),
            ]);

            return [
                'success'  => true,
                'url'      => $this->generateUrl(
                    'victoire_core_page_show',
                    [
                        '_locale' => $request->getLocale(),
                        'url'     => $viewReference->getUrl(),
                    ]
                ),
            ];
        } else {
            $formErrorHelper = $this->get('victoire_form.error_helper');

            return [
                'success' => !$form->isSubmitted(),
                'message' => $formErrorHelper->getRecursiveReadableErrors($form),
                'html'    => $this->get('templating')->render(
                    $this->getBaseTemplatePath().':new.html.twig',
                    ['form' => $form->createView()]
                ),
            ];
        }
    }

    /**
     * Page settings.
     *
     * @param Request  $request
     * @param BasePage $page
     *
     * @return array
     */
    protected function settingsAction(Request $request, BasePage $page)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm($this->getPageSettingsType(), $page);
        $businessProperties = [];

        //if the page is a business entity page pattern
        if ($page instanceof BusinessTemplate) {
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($page->getBusinessEntityId());
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        //if the page is a business entity page
        if ($page instanceof BusinessPage) {
            $form = $this->createForm($this->getBusinessPageType(), $page);
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager->persist($page);
            $entityManager->flush();
            /** @var ViewReference $viewReference */
            $viewReference = $this->get('victoire_view_reference.repository')
                ->getOneReferenceByParameters(['viewId' => $page->getId(), 'locale' => $request->getLocale()]);

            $page->setReference($viewReference);

            $this->congrat($this->get('translator')->trans('victoire_page.update.success', [], 'victoire'));

            return [
                'success' => true,
                'url'     => $this->generateUrl(
                    'victoire_core_page_show', [
                        '_locale' => $request->getLocale(),
                        'url'     => $viewReference->getUrl(),
                    ]
                ),
            ];
        }
        //we display the form
        $errors = $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form);

        return  [
            'success' => empty($errors),
            'html'    => $this->get('templating')->render(
                $this->getBaseTemplatePath().':settings.html.twig',
                [
                    'page'               => $page,
                    'form'               => $form->createView(),
                    'businessProperties' => $businessProperties,
                ]
            ),
            'message' => $errors,
        ];
    }

    /**
     * @param Page $page The page to delete
     *
     * @return Response
     */
    public function deleteAction(BasePage $page)
    {
        $response = null;

        try {
            //it should not be allowed to try to delete an undeletable page
            if ($page->isUndeletable()) {
                $message = $this->get('translator')->trans('page.undeletable', [], 'victoire');
                throw new \Exception($message);
            }

            //the entity manager
            $entityManager = $this->get('doctrine.orm.entity_manager');

            //remove the page
            $entityManager->remove($page);

            //flush the modifications
            $entityManager->flush();

            //redirect to the homepage

            $homepageUrl = $this->generateUrl('victoire_core_homepage_show');

            $response = [
                'success' => true,
                'url'     => $homepageUrl,
            ];
        } catch (\Exception $ex) {
            $response = [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }

        return $response;
    }
}
