<?php
namespace Victoire\Bundle\CoreBundle\Tests\Listener;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;
use Victoire\Bundle\CoreBundle\Event\PageMenuContextualEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Tests the CmsExtension class
 */
class SiteMapMenuListenerTest extends BaseTestCase
{

    /**
     * create an admin menu defined in the contructor
     */
    public function testAddGlobal()
    {
        $siteMapMenuListenerMock = $this->getSiteMapMenuListenerMock();
        $event = new Event();
        $response = $siteMapMenuListenerMock->addGlobal($event);

        $this->assertEquals('menu.sitemap', $response->getName());
    }

}
