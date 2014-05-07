<?php
namespace Victoire\Bundle\CoreBundle\Tests\Listener;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;
use Victoire\Bundle\CoreBundle\Event\Menu\BasePageMenuContextualEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Tests the CmsExtension class
 */
class PageMenuListenerTest extends BaseTestCase
{

    /**
     * create an admin menu defined in the contructor
     */
    public function testAddGlobal()
    {
        $pageMenuListenerMock = $this->getPageMenuListenerMock();
        $event = new Event();
        $response = $pageMenuListenerMock->addGlobal($event);

        $this->assertEquals('menu.page', $response->getName());

    }

    /**
     * create an admin menu defined in the contructor
     */
    public function testAddContextual()
    {

        $pageMenuListenerMock = $this->getPageMenuListenerMock();
        $event = new Event();
        $pageMenuListenerMock->addGlobal($event);

        $pageMock = $this->getPageMock();
        $event = new BasePageMenuContextualEvent($pageMock);
        $response = $pageMenuListenerMock->addContextual($event);

        $this->assertEquals('menu.page', $response->getName());

    }


}
