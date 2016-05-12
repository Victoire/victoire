<?php

namespace Victoire\Bundle\BusinessPageBundle\Builder;

use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityBundle\Provider\EntityProxyProvider;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Exception\IdentifierNotDefinedException;
use Victoire\Bundle\CoreBundle\Helper\UrlBuilder;
use Victoire\Bundle\I18nBundle\Entity\ViewTranslation;
use Victoire\Bundle\ViewReferenceBundle\Builder\ViewReferenceBuilder;

/**
 * @property mixed entityProxyProvider
 */
class BusinessPageBuilder
{
    protected $businessEntityHelper;
    protected $urlBuilder;
    protected $parameterConverter;
    protected $entityProxyProvider;
    protected $viewReferenceBuilder;

    //@todo Make it dynamic please
    protected $pageParameters = [
        'name',
        'bodyId',
        'bodyClass',
        'slug',
        'currentLocale',
    ];

    /**
     * @param BusinessEntityHelper $businessEntityHelper
     * @param UrlBuilder           $urlBuilder
     * @param ParameterConverter   $parameterConverter
     * @param EntityProxyProvider  $entityProxyProvider
     */
    public function __construct(BusinessEntityHelper $businessEntityHelper,
                                UrlBuilder $urlBuilder,
                                ParameterConverter $parameterConverter,
                                EntityProxyProvider $entityProxyProvider,
                                ViewReferenceBuilder $viewReferenceBuilder
    ) {
        $this->businessEntityHelper = $businessEntityHelper;
        $this->urlBuilder = $urlBuilder;
        $this->parameterConverter = $parameterConverter;
        $this->entityProxyProvider = $entityProxyProvider;
        $this->viewReferenceBuilder = $viewReferenceBuilder;
    }

    /**
     * Generate update the page parameters with the entity.
     *
     * @param BusinessTemplate $businessTemplate
     * @param entity           $entity
     *
     * @return VirtualBusinessPage
     */
    public function generateEntityPageFromTemplate(BusinessTemplate $businessTemplate, $entity, EntityManager $em)
    {
        $viewTranslations = $em->getRepository(ViewTranslation::class)->getTranslationForView($businessTemplate);
        $translations = [];
        if (count($viewTranslations == 0)) {
            return $this->legacyGenerateEntityPageFromTemplate($businessTemplate, $entity, $em);
        }
        $page = new VirtualBusinessPage();

        $pageLocale = $businessTemplate->getLocale();
        foreach (array_reverse($viewTranslations) as $viewTranslation) {
            $page = new VirtualBusinessPage();
            $em->refresh($viewTranslation->getObject()->setTranslatableLocale($viewTranslation->getLocale()));
            $reflect = new \ReflectionClass($businessTemplate);
            $templateProperties = $reflect->getProperties();
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($templateProperties as $property) {
                if (!in_array(
                        $property->getName(),
                        ['id', 'widgetMap', 'slots', 'seo', 'i18n', 'widgets']
                    ) && !$property->isStatic()
                ) {
                    $value = $accessor->getValue($viewTranslation->getObject(), $property->getName());
                    $setMethod = 'set'.ucfirst($property->getName());
                    if (method_exists($page, $setMethod)) {
                        $accessor->setValue($page, $property->getName(), $value);
                    }
                }
            }
            $translations[$viewTranslation->getLocale()]['page'] = $page;
        }

        //find Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity object according to the given $entity
        $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);

        if ($businessEntity !== null) {
            //the business properties usable in a url
            $businessProperties = $this->getBusinessProperties($businessEntity);

            $entityProxy = $this->entityProxyProvider->getEntityProxy($entity, $businessEntity, $em);

            $references = [];
            foreach ($translations as $locale => $translation) {
                /** @var VirtualBusinessPage $page */
                $page = $translation['page'];
                //the url of the page
                $pageUrl = $this->urlBuilder->buildUrl($page);

            $pageName = $page->getName();
            $pageSlug = $page->getSlug();

            //parse the business properties
            foreach ($businessProperties as $businessProperty) {
                $pageUrl = $this->parameterConverter->setBusinessPropertyInstance($pageUrl, $businessProperty, $entity);
                $pageSlug = $this->parameterConverter->setBusinessPropertyInstance($pageSlug, $businessProperty, $entity);
                $pageName = $this->parameterConverter->setBusinessPropertyInstance($pageName, $businessProperty, $entity);
            }

            //Check that all twig variables in pattern url was removed for it's generated BusinessPage
            preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $pageUrl, $matches);

            if (count($matches[2])) {
                throw new IdentifierNotDefinedException($matches[2]);
            }

            $entityProxy = $this->entityProxyProvider->getEntityProxy($entity, $businessEntity, $em);
            //we update the url of the page
            $page->setCurrentLocale($businessTemplate->getCurrentLocale());
            $page->setUrl($pageUrl);
            $page->setSlug($pageSlug);
            $page->setName($pageName);
            $page->setEntityProxy($entityProxy);
            $page->setTemplate($businessTemplate);
            $page->setReferences([$page->getCurrentLocale() => $this->viewReferenceBuilder->buildViewReference($page, $em)]);

            if ($seo = $businessTemplate->getSeo()) {
                $pageSeo = clone $seo;
                $page->setSeo($pageSeo);
            }
        }

        return $page;
    }

    /**
     * Get the list of business properties usable for the url.
     *
     * @param BusinessEntity $businessEntity
     *
     * @return BusinessProperty[] The list of business properties
     */
    public function getBusinessProperties(BusinessEntity $businessEntity)
    {
        //the business properties usable in a url
        $businessProperties = $businessEntity->getBusinessPropertiesByType('businessParameter');

        //the business properties usable in a url
        $seoBusinessProps = $businessEntity->getBusinessPropertiesByType('seoable');

        //the business properties are the identifier and the seoables properties
        $businessProperties = array_merge($businessProperties, $seoBusinessProps);

        return $businessProperties;
    }

    /**
     * Generate update the page parameters with the entity.
     *
     * @param BusinessPage $page
     * @param Entity       $entity
     */
    public function updatePageParametersByEntity(BusinessPage $page, $entity)
    {
        //if no entity is provided
        if ($entity === null) {
            //we look for the entity of the page
            if ($page->getBusinessEntity() !== null) {
                $entity = $page->getBusinessEntity();
            }
        }

        //only if we have an entity instance
        if ($entity !== null) {
            $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);

            if ($businessEntity !== null) {
                $businessProperties = $this->getBusinessProperties($businessEntity);

                //parse the business properties
                foreach ($businessProperties as $businessProperty) {
                    //parse of seo attributes
                    foreach ($this->pageParameters as $pageAttribute) {
                        $string = $this->getEntityAttributeValue($page, $pageAttribute);
                        $updatedString = $this->parameterConverter->setBusinessPropertyInstance($string, $businessProperty, $entity);
                        $this->setEntityAttributeValue($page, $pageAttribute, $updatedString);
                    }
                }
            }
        }
    }

    /**
     * Get the content of an attribute of an entity given.
     *
     * @param BusinessPage $entity
     * @param string       $field
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($entity, $field)
    {
        $functionName = 'get'.ucfirst($field);

        $fieldValue = call_user_func([$entity, $functionName]);

        return $fieldValue;
    }

    /**
     * Update the value of the entity.
     *
     * @param BusinessPage $entity
     * @param string       $field
     * @param string       $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func([$entity, $functionName], $value);
    }

    private function legacyGenerateEntityPageFromTemplate(BusinessTemplate $businessTemplate, $entity, EntityManager $em)
    {
        $page = new VirtualBusinessPage();
        $reflect = new \ReflectionClass($businessTemplate);
        $templateProperties = $reflect->getProperties();
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($templateProperties as $property) {
            if (!in_array($property->getName(), ['id', 'widgetMap', 'slots', 'seo', 'i18n', 'widgets']) && !$property->isStatic()) {
                $value = $accessor->getValue($businessTemplate, $property->getName());
                $setMethod = 'set'.ucfirst($property->getName());
                if (method_exists($page, $setMethod)) {
                    $accessor->setValue($page, $property->getName(), $value);
                }
            }
        }
        //find Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity object according to the given $entity
        $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);
        if ($businessEntity !== null) {
            //the business properties usable in a url
            $businessProperties = $this->getBusinessProperties($businessEntity);
            //the url of the page
            $pageUrl = $this->urlBuilder->buildUrl($page);
            $pageName = $page->getName();
            $pageSlug = $page->getSlug();
            //parse the business properties
            foreach ($businessProperties as $businessProperty) {
                $pageUrl = $this->parameterConverter->setBusinessPropertyInstance($pageUrl, $businessProperty, $entity);
                $pageSlug = $this->parameterConverter->setBusinessPropertyInstance($pageSlug, $businessProperty, $entity);
                $pageName = $this->parameterConverter->setBusinessPropertyInstance($pageName, $businessProperty, $entity);
            }
            //Check that all twig variables in pattern url was removed for it's generated BusinessPage
            preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $pageUrl, $matches);
            if (count($matches[2])) {
                throw new IdentifierNotDefinedException($matches[2]);
            }
            $entityProxy = $this->entityProxyProvider->getEntityProxy($entity, $businessEntity, $em);
            //we update the url of the page
            $page->setTranslatableLocale($businessTemplate->getLocale());
            $page->setUrl($pageUrl);
            $page->setSlug($pageSlug);
            $page->setName($pageName);
            $page->setEntityProxy($entityProxy);
            $page->setTemplate($businessTemplate);
            $page->setReferences([$page->getLocale() => $this->viewReferenceBuilder->buildViewReference($page, $em)]);
            if ($seo = $businessTemplate->getSeo()) {
                $pageSeo = clone $seo;
                $page->setSeo($pageSeo);
            }
        }

        return $page;
    }
}
