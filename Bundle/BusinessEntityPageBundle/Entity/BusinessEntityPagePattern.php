<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * BusinessEntityPagePattern
 *
 * @ORM\Table("vic_page_business_entity_page_pattern")
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityPageBundle\Repository\BusinessEntityPagePatternRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BusinessEntityPagePattern extends BasePage
{
    //This trait add the query and business_entity_name columns
    use \Victoire\Bundle\QueryBundle\Entity\Traits\QueryTrait;

    const TYPE = 'business_entity_page_pattern';
}
