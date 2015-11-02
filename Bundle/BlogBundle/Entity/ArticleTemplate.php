<?php

namespace Victoire\Bundle\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;

/**
 * ArticleTemplate (extends BusinessTemplate).
 *
 * @ORM\Entity(repositoryClass="Victoire\Bundle\BlogBundle\Repository\ArticleTemplateRepository")
 */
class ArticleTemplate extends BusinessTemplate
{
    const TYPE = 'article_template';

    /**
     * Get query.
     *
     * @return string
     */
    public function additionnalQueryPart()
    {
        return sprintf('%s main_item.template = %s', $this->query ? $this->query.' AND ' : 'WHERE ', $this->getId());
    }
}
