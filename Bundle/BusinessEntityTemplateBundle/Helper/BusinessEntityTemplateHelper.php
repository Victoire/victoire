<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Helper;


use Victoire\Bundle\CoreBundle\Entity\BusinessEntity;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author Thomas Beaujean
 *
 */
class BusinessEntityTemplateHelper
{
    protected $em = null;

    /**
     * Cosntructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Find the templates by the business entity
     *
     * @param BusinessEntity $businessEntity
     *
     * @return array of business entity template
     */
    public function findTemplatesByBusinessEntity(BusinessEntity $businessEntity)
    {
        //services
        $em = $this->em;

        //the repository
        $repository = $em->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate');

        //retrieve the templates
        $templates = $repository->findTemplatesByBusinessEntity($businessEntity);

        return $templates;
    }
}