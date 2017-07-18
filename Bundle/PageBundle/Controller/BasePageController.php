<?php

namespace Victoire\Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\CoreBundle\Controller\VictoireAlertifyControllerTrait;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * The BasePage controller is used to interact with all kind of pages.
 **/
class BasePageController extends Controller
{
    use VictoireAlertifyControllerTrait;

    /**
     * Find Page from url and render it.
     * Route for this action is defined in RouteLoader.
     *
     * @param Request $request
     * @param string  $url
     *
     * @return mixed
     */
    public function showAction(Request $request, $url = '')
    {
        $response = $this->get('victoire_page.page_helper')->renderPageByUrl(
            $url,
            $request->getLocale(),
            $request->isXmlHttpRequest() ? $request->query->get('modalLayout', null) : null
        );

        return $response;
    }

    /**
     * Find url for a View id and optionally an Entity id and redirect to showAction.
     * Route for this action is defined in RouteLoader.
     *
     * @param Request $request
     * @param $viewId
     * @param null $entityId
     *
     * @return RedirectResponse
     */
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

    /**
     * Find BusinessPage url for an Entity id and type and redirect to showAction.
     * Route for this action is defined in RouteLoader.
     *
     * @param Request $request
     * @param $entityId
     * @param $type
     *
     * @return RedirectResponse
     */
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
     * Display a form to create a new Blog or Page.
     * Route is defined in inherited controllers.
     *
     * @param Request $request
     * @param bool    $isHomepage
     *
     * @return array
     */
    protected function newAction(Request $request, $isHomepage = false)
    {
        $page = $this->getNewPage();
        if ($page instanceof Page) {
            $page->setHomepage($isHomepage ? $isHomepage : 0);
        }

        $form = $this->get('form.factory')->create($this->getNewPageType(), $page);

        return [
            'success' => true,
            'html'    => $this->get('templating')->render(
                $this->getBaseTemplatePath().':new.html.twig',
                ['form' => $form->createView()]
            ),
        ];
    }

    /**
     * Create a new Blog or Page.
     * Route is defined in inherited controllers.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function newPostAction(Request $request)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $page = $this->getNewPage();

        $form = $this->get('form.factory')->create($this->getNewPageType(), $page);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $page = $this->get('victoire_page.page_helper')->setPosition($page);
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

            return $this->getViewReferenceRedirect($request, $page);
        }

        return [
            'success' => false,
            'message' => $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form),
            'html'    => $this->get('templating')->render(
                $this->getBaseTemplatePath().':new.html.twig',
                ['form' => $form->createView()]
            ),
        ];
    }

    /**
     * Display a form to edit Page settings.
     * Route is defined in inherited controllers.
     *
     * @param Request  $request
     * @param BasePage $page
     *
     * @return array
     */
    protected function settingsAction(Request $request, BasePage $page)
    {
        $form = $this->createSettingsForm($page);

        return  [
            'success' => true,
            'html'    => $this->get('templating')->render(
                $this->getBaseTemplatePath().':settings.html.twig',
                [
                    'page'               => $page,
                    'form'               => $form->createView(),
                    'businessProperties' => $this->getBusinessProperties($page),
                ]
            ),
        ];
    }

    /**
     * Save Page settings.
     * Route is defined in inherited controllers.
     *
     * @param Request  $request
     * @param BasePage $page
     *
     * @return array
     */
    protected function settingsPostAction(Request $request, BasePage $page)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createSettingsForm($page);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager->persist($page);
            $entityManager->flush();

            $this->congrat($this->get('translator')->trans('victoire_page.update.success', [], 'victoire'));

            return $this->getViewReferenceRedirect($request, $page);
        }

        return  [
            'success' => false,
            'message' => $this->get('victoire_form.error_helper')->getRecursiveReadableErrors($form),
            'html'    => $this->get('templating')->render(
                $this->getBaseTemplatePath().':settings.html.twig',
                [
                    'page'               => $page,
                    'form'               => $form->createView(),
                    'businessProperties' => $this->getBusinessProperties($page),
                ]
            ),
        ];
    }

    /**
     * Delete a Page.
     * Route is defined in inherited controllers.
     *
     * @param BasePage $page
     *
     * @return array
     */
    protected function deleteAction(BasePage $page)
    {
        try {
            //Throw Exception if Page is undeletable
            if ($page->isUndeletable()) {
                $message = $this->get('translator')->trans('page.undeletable', [], 'victoire');
                throw new \Exception($message);
            }

            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->remove($page);
            $entityManager->flush();

            return [
                'success' => true,
                'url'     => $this->generateUrl('victoire_core_homepage_show'),
            ];
        } catch (\Exception $ex) {
            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }

    /**
     * Return an array for JsonResponse redirecting to a ViewReference.
     *
     * @param Request  $request
     * @param BasePage $page
     *
     * @return array
     */
    protected function getViewReferenceRedirect(Request $request, BasePage $page)
    {
        $parameters = [
            'viewId' => $page->getId(),
        ];

        if (!($page instanceof Blog)) {
            $parameters['locale'] = $request->getLocale();
        }

        $viewReference = $this->get('victoire_view_reference.repository')
            ->getOneReferenceByParameters($parameters);

        $page->setReference($viewReference);

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
    }

    /**
     * Create Settings form according to Page type.
     *
     * @param BasePage $page
     *
     * @return Form
     */
    protected function createSettingsForm(BasePage $page)
    {
        $form = $this->createForm($this->getPageSettingsType(), $page);

        if ($page instanceof BusinessPage) {
            $form = $this->createForm($this->getBusinessPageType(), $page);
        }

        return $form;
    }

    /**
     * Return BusinessEntity seaoable properties if Page is a BusinessTemplate.
     *
     * @param BasePage $page
     *
     * @return array
     */
    protected function getBusinessProperties(BasePage $page)
    {
        $businessProperties = [];

        if ($page instanceof BusinessTemplate) {
            //we can use the business entity properties on the seo
            $businessEntity = $this->get('victoire_core.helper.business_entity_helper')->findById($page->getBusinessEntityId());
            $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');
        }

        return $businessProperties;
    }
}
