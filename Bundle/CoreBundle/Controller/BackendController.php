<?php

namespace Victoire\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Backend controller.
 */
abstract class BackendController extends Controller
{
    use VictoireAlertifyControllerTrait;

}
