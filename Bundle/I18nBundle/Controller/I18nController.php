<?php
namespace Victoire\Bundle\i18nBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class I18nController
{
    public function indexAction($name)
    {
        return new Response('<html><body>Hello '.$name.'!</body></html>');
    }
}