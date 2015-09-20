<?php
namespace Victoire\Bundle\BlogBundle\Manager;


use Doctrine\ORM\EntityManager;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Manager\Interfaces\BlogReferenceBuilderInterface;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Manager\BaseReferenceBuilder;

class ArticleTemplateReferenceBuilder extends BaseReferenceBuilder
{
    public function buildReference(View $view, EntityManager $em) {

        return array();
    }
}
