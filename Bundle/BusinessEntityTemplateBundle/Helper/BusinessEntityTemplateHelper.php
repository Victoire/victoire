<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Helper;

use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplatePage;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Victoire\Bundle\QueryBundle\Helper\QueryHelper;

/**
 *
 * @author Thomas Beaujean
 *
 * ref:victoire_business_entity_template.business_entity_template_helper
 */
class BusinessEntityTemplateHelper
{
    protected $queryHelper = null;

    /**
     *
     * @param QueryHelper $queryHelper
     */
    public function __construct(QueryHelper $queryHelper)
    {
        $this->queryHelper = $queryHelper;
    }

    /**
     * Is the entity allowed for the business entity template page
     *
     * @param BusinessEntityTemplatePage $page
     * @param unknown $entity
     * @throws \Exception
     * @return boolean
     */
    public function isEntityAllowed(BusinessEntityTemplatePage $page, $entity)
    {
        $allowed = true;

        //test that an entity is given
        if ($entity === null) {
            throw new \Exception('The entity is mandatory.');
        }

        $businessEntityTemplate = $page->getBusinessEntityTemplate();

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
     * @param BusinessEntityTemplatePage $page
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
}