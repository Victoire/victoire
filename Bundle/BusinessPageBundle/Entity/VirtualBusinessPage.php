<?php
namespace Victoire\Bundle\BusinessPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\CoreBundle\Entity\BaseEntityProxy;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * VirtualBusinessPage
 *
 * @ORM\MappedSuperclass()
 */
class VirtualBusinessPage extends BusinessPage
{
    const TYPE = 'virtual_business_page';
}
