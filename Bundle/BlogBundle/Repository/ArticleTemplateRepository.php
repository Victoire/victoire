<?php

namespace Victoire\Bundle\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The Article Template repository.
 */
class ArticleTemplateRepository extends EntityRepository {

    use StateFullRepositoryTrait;

}
