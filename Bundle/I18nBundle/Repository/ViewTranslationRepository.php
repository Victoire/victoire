<?php

namespace Victoire\Bundle\I18nBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Repository\StateFullRepositoryTrait;

/**
 * The ViewTranslation repository.
 */
class ViewTranslationRepository extends EntityRepository
{
    use StateFullRepositoryTrait;

    public function getTranslationForView(View $view)
    {
        if ($view instanceof VirtualBusinessPage) {
            $view = $view->getTemplate();
        }
        return $this->findBy(['object' => $view]);
    }
}
