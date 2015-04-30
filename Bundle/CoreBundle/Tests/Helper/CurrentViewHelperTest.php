<?php

namespace Victoire\Bundle\CoreBundle\Tests\Helper;

use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;

class CurrentViewHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCurrentView()
    {
        $currentViewHelper = new CurrentViewHelper();
        $this->assertEquals(null, $currentViewHelper->getCurrentView());
    }
}
