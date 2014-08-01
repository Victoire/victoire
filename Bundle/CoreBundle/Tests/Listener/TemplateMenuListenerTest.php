<?php
namespace Victoire\Bundle\CoreBundle\Tests\Listener;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;
use Victoire\Bundle\PageBundle\Event\Menu\TemplateMenuContextualEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Tests the CmsExtension class
 */
class TemplateMenuListenerTest extends BaseTestCase
{

    /**
     * create an admin menu defined in the contructor
     */
    public function testAddGlobal()
    {
        $templateMenuListenerMock = $this->getTemplateMenuListenerMock();
        $event = new Event();
        $response = $templateMenuListenerMock->addGlobal($event);

        $this->assertEquals('menu.template', $response->getName());

    }

    /**
     * create an admin menu defined in the contructor
     */
    public function testAddContextual()
    {

        $templateMenuListenerMock = $this->getTemplateMenuListenerMock();
        $event = new Event();
        $templateMenuListenerMock->addGlobal($event);

        $templateMock = $this->getTemplateMock();
        $event = new TemplateMenuContextualEvent($templateMock);
        $response = $templateMenuListenerMock->addContextual($event);

        $this->assertEquals('menu.template', $response->getName());

    }

}
