<?php

namespace Victoire\Bundle\SeoBundle\Helper;

use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;

/**
 * The seo helper brings some seo functions for pages
 * ref: victoire_seo.helper.pageseo_helper
 */
class PageSeoHelper
{
    protected $businessEntityHelper = null;
    protected $parameterConverter = null;

    protected $pageSeoAttributes = array(
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
        'keyword');

    /**
     * Constructor
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
     * Generate a seo for the page using the current entity
     *
     * @param Page   $page
     * @param Entity $entity
     */
    public function updateSeoByEntity(Page $page, $entity)
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
                $className = get_class($entity);

                $businessEntity = $this->businessEntityHelper->findByClassname($className);

                if ($businessEntity !== null) {

                    $businessProperties = $businessEntity->getBusinessPropertiesByType('seoable');

                    //parse the business properties
                    foreach ($businessProperties as $businessProperty) {
                        //parse of seo attributes
                        foreach ($this->pageSeoAttributes as $seoAttribute) {
                            $string = $this->getEntityAttributeValue($pageSeo, $seoAttribute);
                            $updatedString = $this->parameterConverter->setBusinessPropertyInstance($string, $businessProperty, $entity);
                            $this->setEntityAttributeValue($pageSeo, $seoAttribute, $updatedString);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the content of an attribute of an entity given
     *
     * @param entity $entity
     * @param string $field
     *
     * @return mixed
     */
    protected function getEntityAttributeValue($entity, $field)
    {
        $functionName = 'get'.ucfirst($field);

        $fieldValue = call_user_func(array($entity, $functionName));

        return $fieldValue;
    }

    /**
     * Update the value of the entity
     *
     * @param Object $entity
     * @param string $field
     * @param string $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func(array($entity, $functionName), $value);
    }
}
