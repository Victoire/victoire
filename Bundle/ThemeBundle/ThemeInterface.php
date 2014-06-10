<?php

namespace Victoire\Bundle\ThemeBundle;

use Victoire\Bundle\CoreBundle\Widget\Managers\ManagerInterface;

interface ThemeInterface
{

    /**
     * theme name
     *
     **/
    public function __construct(ManagerInterface $themeManager);
    /**
     * theme name
     *
     **/
    public static function getName();

    /**
     * theme label
     *
     **/
    public static function getLabel();

    /**
     * theme entity class
     *
     **/
    public static function getClass();

    /**
     * theme entity class
     *
     **/
    public function getManager();
}
