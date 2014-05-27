<?php
namespace Victoire\Bundle\BusinessEntityTemplateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\Page;

/**
 * Page
 *
 * @ORM\Entity
 */
class BusinessEntityTemplatePage extends Page
{
    const TYPE = 'businessEntityTemplate';

}
