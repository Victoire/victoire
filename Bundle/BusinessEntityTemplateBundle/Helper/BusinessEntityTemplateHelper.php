<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Helper;

use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;

/**
 *
 * @author Thomas Beaujean
 *
 * ref: victoire_business_entity_template.business_entity_template_helper
 */
class BusinessEntityTemplateHelper
{
    protected $queryHelper = null;
    protected $businessEntityHelper = null;
    protected $parameterConverter = null;

    /**
     *
     * @param QueryHelper          $queryHelper
     * @param BusinessEntityHelper $businessEntityHelper
     * @param ParameterConverter   $parameterConverter
     *
     */
    public function __construct(QueryHelper $queryHelper, BusinessEntityHelper $businessEntityHelper, ParameterConverter $parameterConverter)
    {
        $this->queryHelper = $queryHelper;
        $this->businessEntityHelper = $businessEntityHelper;
        $this->parameterConverter = $parameterConverter;
    }

    /**
     * Is the entity allowed for the business entity template page
     *
     * @param  BusinessEntityTemplate $businessEntityTemplate
     * @param  unknown                $entity
     * @throws \Exception
     * @return boolean
     */
    public function isEntityAllowed(BusinessEntityTemplate $businessEntityTemplate, $entity)
    {
        $allowed = true;

        //test that an entity is given
        if ($entity === null) {
            throw new \Exception('The entity is mandatory.');
        }

        $queryHelper = $this->queryHelper;

        //the page id
        $entityId = $entity->getId();

        //the base of the query
        $baseQuery = $queryHelper->getQueryBuilder($businessEntityTemplate);

        $baseQuery->andWhere('main_item.id = ' . $entityId);

        //filter with the query of the page
        $items =  $queryHelper->getResultsAddingSubQuery($businessEntityTemplate, $baseQuery);

        //only one page can be found because we filter on the
        if (count($items) > 1) {
            throw new \Exception('More than 1 item was found, there should be 0 or 1 item with this query.');
        }

        if (count($items) === 0) {
            $allowed = false;
        }

        return $allowed;
    }

    /**
     * Get the list of entities allowed for the businessEntityTemplate page
     *
     * @param  BusinessEntityTemplate $page
     * @throws \Exception
     * @return boolean
     */
    public function getEntitiesAllowed(BusinessEntityTemplate $businessEntityTemplate)
    {
        $queryHelper = $this->queryHelper;

        //the base of the query
        $baseQuery = $queryHelper->getQueryBuilder($businessEntityTemplate);

        // add this fake condition to ensure that there is always a "where" clause.
        // In query mode, usage of "AND" will be always valid instead of "WHERE"
        $baseQuery->andWhere('1 = 1');

        //filter with the query of the page
        $items =  $queryHelper->getResultsAddingSubQuery($businessEntityTemplate, $baseQuery);

        return $items;
    }

    /**
     * Generate update the page parameters with the entity
     *
     * @param Page   $page
     * @param Entity $entity
     */
    public function fillEntityPageVariables(Page $page, $entity)
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
            $className = get_class($entity);

            $businessEntity = $this->businessEntityHelper->findByClassname($className);

            if ($businessEntity !== null) {
                //the business properties usable in a url
                $businessProperties = $this->getBusinessProperties($businessEntity);

                //the url of the page
                $pageUrl = $page->getUrl();
                $pageTitle = $page->getTitle();

                //parse the business properties
                foreach ($businessProperties as $businessProperty) {
                    $pageUrl = $this->parameterConverter->setBusinessPropertyInstance($pageUrl, $businessProperty, $entity);
                    $pageTitle = $this->parameterConverter->setBusinessPropertyInstance($pageTitle, $businessProperty, $entity);
                }

                //we update the url of the page
                $page->setUrl($pageUrl);
                $page->setTitle($pageTitle);
            }
        }
    }

    /**
     * Get the list of business properties usable for the url
     *
     * @param BusinessEntity $businessEntity
     *
     * @return array The list of business properties
     */
    public function getBusinessProperties(BusinessEntity $businessEntity)
    {
        //the business properties usable in a url
        $businessProperties = $businessEntity->getBusinessPropertiesByType('businessIdentifier');

        //the business properties usable in a url
        $seoBusinessProperties = $businessEntity->getBusinessPropertiesByType('seoable');

        //the business properties are the identifier and the seoables properties
        $businessProperties = array_merge($businessProperties, $seoBusinessProperties);

        return $businessProperties;
    }

    /**
     * Get the position of the identifier in the url of a business entity template
     *
     * @param BusinessEntityTemplate $businessEntityTemplate
     *
     * @return integer The position
     */
    public function getIdentifierPositionInUrl(BusinessEntityTemplate $businessEntityTemplate)
    {
        $position = null;

        $url = $businessEntityTemplate->getUrl();

        // split on the / character
        $keywords = preg_split("/\//", $url);

        //the business property link to the page
        $businessEntityId = $businessEntityTemplate->getBusinessEntityName();

        $businessEntity = $this->businessEntityHelper->findById($businessEntityId);

        //the business properties usable in a url
        $businessProperties = $businessEntity->getBusinessPropertiesByType('businessIdentifier');

        //we parse the words of the url
        foreach ($keywords as $index => $keyword) {
            foreach ($businessProperties as $businessProperty) {
                $entityProperty = $businessProperty->getEntityProperty();
                $searchWord = '{{item.'.$entityProperty.'}}';

                if ($searchWord === $keyword) {
                    //the array start at index 0 but we want the position to start at 1
                    $position = array(
                        'position' => $index + 1,
                        'businessProperty' => $businessProperty
                    );
                }
            }
        }

        return $position;
    }
}
