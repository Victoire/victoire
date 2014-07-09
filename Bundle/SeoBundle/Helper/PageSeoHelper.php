<?php

namespace Victoire\Bundle\SeoBundle\Helper;


use Victoire\Bundle\PageBundle\Entity\BasePage;
use Victoire\Bundle\CoreBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\CoreBundle\Entity\BusinessProperty;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_seo.helper.pageseo_helper
 */
class PageSeoHelper
{
    protected $businessEntityHelper = null;

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
     */
    public function __construct(BusinessEntityHelper $businessEntityHelper)
    {
        $this->businessEntityHelper = $businessEntityHelper;
    }

    /**
     * Generate a seo for the page using the current entity
     *
     * @param BasePage $page
     * @param Entity   $entity
     */
    public function updateSeoByEntity(BasePage $page, $entity)
    {
        //if no entity is provided
        if ($entity === null) {
            //we look for the entity of the page
            if ($page->getEntity() !== null) {
                $entity = $page->getEntity();
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
                            $updatedString = $this->setBusinessPropertyInstance($string, $businessProperty, $entity);
                            $this->setEntityAttributeValue($pageSeo, $seoAttribute, $updatedString);
                        }
                    }
                }
            }
        }
    }

    /**
     * Replace the code string with the value of the entity attribute
     *
     * @param The string       $string
     * @param BusinessProperty $businessProperty
     * @param Object           $entity
     *
     * @throws \Exception
     *
     * @return string The updated string
     */
    protected function setBusinessPropertyInstance($string, BusinessProperty $businessProperty, $entity)
    {
        //test parameters
        if ($entity === null) {
            throw new \Exception('The parameter entity can not be null');
        }

        //the attribute to set
        $entityProperty = $businessProperty->getEntityProperty();

        //the string to replace
        $stringToReplate = '{{item.'.$entityProperty.'}}';

        //the value of the attribute
        $attributeValue = $this->getEntityAttributeValue($entity, $entityProperty);

        //we provide a default value
        if ($attributeValue === null) {
            $attributeValue = '';
        }

        //we replace the string
        $string = str_replace($stringToReplate, $attributeValue, $string);

        return $string;
    }


    /**
     * Get the content of an attribute of an entity given
     *
     * @param entity $entity
     * @param strin $functionName
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
     * @param Object        $entity
     * @param The attribute $field
     * @param string        $value
     *
     * @return mixed
     */
    protected function setEntityAttributeValue($entity, $field, $value)
    {
        $functionName = 'set'.ucfirst($field);

        call_user_func(array($entity, $functionName), $value);
    }
}