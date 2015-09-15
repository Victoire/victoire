<?php
namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;

/**
 * ArticleTemplate (extends BusinessTemplate)
 *
 * @ORM\Entity
 */
class ArticleTemplate extends BusinessTemplate
{
    const TYPE = 'article_template';

    /**
     * Get query
     *
     * @return string
     */
    public function additionnalQueryPart()
    {
        return sprintf("%s main_item.pattern = %s", $this->query ? $this->query . " AND " : "WHERE ", $this->getId());
    }
}
