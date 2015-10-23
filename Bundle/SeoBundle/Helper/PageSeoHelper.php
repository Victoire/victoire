<?php

namespace Victoire\Bundle\SeoBundle\Helper;

use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * The seo helper brings some seo functions for pages
 * ref: victoire_seo.helper.pageseo_helper.
 */
class PageSeoHelper
{
    protected $businessEntityHelper = null;
    protected $parameterConverter = null;

    protected $pageSeoAttributes = [
        'metaTitle',
        'metaDescription',
        'relAuthor',
        'relPublisher',
        'ogTitle',
        'ogType',
        'ogImage',
        'ogUrl',
        'ogDescription',
        'fbAdmins',
        'twitterCard',
        'twitterUrl',
        'twitterTitle',
        'twitterDescription',
        'twitterImage',
        'schemaPageType',
        'schemaName',
        'schemaDescription',
        'schemaImage',
        'metaRobotsIndex',
        'metaRobotsFollow',
        'metaRobotsAdvanced',
        'relCanonical',
        'keyword', ];

    /**
     * Constructor.
     *
     * @param BusinessEntityHelper $businessEntityHelper
     * @param ParameterConverter   $parameterConverter
     */
    public function __construct(BusinessEntityHelper $businessEntityHelper, ParameterConverter $parameterConverter)
    {
        $this->businessEntityHelper = $businessEntityHelper;
        $this->parameterConverter = $parameterConverter;
    }

    /**
     * Generate a seo for the page using the current entity.
     *
     * @param Page   $page
     * @param Entity $entity
     */
    public function updateSeoByEntity(BasePage $page, $entity)
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
            //the page seo
            $pageSeo = $page->getSeo();

            //the page seo might not exist yet
            if ($pageSeo !== null) {
                $businessEntity = $this->businessEntityHelper->findByEntityInstance($entity);

                if ($businessEntity !== null) {
                    $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');

                    //parse the business properties
                    foreach ($businessProperties as $businessProperty) {
                        //parse of seo attributes
                        foreach ($this->pageSeoAttributes as $seoAttribute) {
                            $value = $this->getEntityAttributeValue($pageSeo, $seoAttribute);
                            // we only update value if its a string and (if its a VBP or its a BP where value is not defined)
                            if (is_string($value) && ($page instanceof VirtualBusinessPage || ($page instanceof BusinessPage && $value == null))) {
                                $value = $this->parameterConverter->setBusinessPropertyInstance(
                                    $value,
                                    $businessProperty,
                                    $entity
                                );
                            }
                            $this->setEntityAttributeValue($pageSeo, $seoAttribute, $value);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the content of an attribute of an entity given.
     *
     * @param \Victoire\Bundle\SeoBundle\Entity\PageSeo $entity
     * @param string                                    $field
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
     * @param \Victoire\Bundle\SeoBundle\Entity\PageSeo $entity
     * @param string                                    $field
     * @param string                                    $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func([$entity, $functionName], $value);
    }
}
