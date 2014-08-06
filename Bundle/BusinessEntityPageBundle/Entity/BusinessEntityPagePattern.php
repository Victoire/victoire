<?php
namespace Victoire\Bundle\BusinessEntityPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * BusinessEntityPagePattern
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BusinessEntityPageBundle\Repository\BusinessEntityPagePatternRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BusinessEntityPagePattern extends BasePage
{
    //This trait add the query and business_entity_name columns
    use \Victoire\Bundle\QueryBundle\Entity\Traits\QueryTrait;

    const TYPE = 'business_entity_page_pattern';

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessEntityPage", mappedBy="pattern")
     */
    protected $instances;

    public function getInstances() { return $this->instances; }
    public function setInstances($instances) { $this->instances = $instances; return $this; }
}
