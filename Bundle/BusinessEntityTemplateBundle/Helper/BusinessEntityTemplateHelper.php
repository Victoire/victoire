<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Helper;

use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;
use Victoire\Bundle\BusinessEntityBundle\Helper\BusinessEntityHelper;
use Victoire\Bundle\PageBundle\Entity\Page;
use Victoire\Bundle\BusinessEntityBundle\Converter\ParameterConverter;

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
     * @param BusinessEntityTemplate $businessEntityTemplate
     * @param unknown $entity
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

        // add this fake condition to ensure that there is always a "where" clause.
        // In query mode, usage of "AND" will be always valid instead of "WHERE"
        $baseQuery->andWhere('1 = 1');

        //we filter on the page id
        $additionnalDql = 'item.id = '.$entityId;

        //filter with the query of the page
        $items =  $queryHelper->getResultsAddingSubQuery($businessEntityTemplate, $baseQuery, $additionnalDql);

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
     * @param BusinessEntityTemplate $page
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
     * @param Page $page
     * @param Entity   $entity
     */
    public function updatePageUrlByEntity(Page $page, $entity)
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
                $businessProperties = $businessEntity->getBusinessPropertiesByType('businessIdentifier');

                //the business properties usable in a url
                $seoBusinessProperties = $businessEntity->getBusinessPropertiesByType('seoable');

                //the business properties are the identifier and the seoables properties
                $businessProperties = array_merge($businessProperties, $seoBusinessProperties);

                //the url of the page
                $pageUrl = $page->getUrl();

                //parse the business properties
                foreach ($businessProperties as $businessProperty) {
                    $pageUrl = $this->parameterConverter->setBusinessPropertyInstance($pageUrl, $businessProperty, $entity);
                }

                //we update the url of the page
                $page->setUrl($pageUrl);
            }
        }
    }
}