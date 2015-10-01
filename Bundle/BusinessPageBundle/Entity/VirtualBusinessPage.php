<?php

namespace Victoire\Bundle\BusinessPageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VirtualBusinessPage.
 *
 * @ORM\MappedSuperclass()
 */
class VirtualBusinessPage extends BusinessPage
{
    const TYPE = 'virtual_business_page';
}
