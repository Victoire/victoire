<?php

namespace Victoire\Bundle\BusinessEntityBundle\Entity;


interface BusinessEntityInterface
{
    public function getSlug();
    public function isVisibleOnFront();
    public function getProxy();
}
