<?php
namespace Victoire\Bundle\CoreBundle\Tests\Widget\Managers;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;

/**
 * Tests the WidgetImageManager class
 */
class WidgetImageManagerTest extends BaseTestCase
{

    /**
     * create a new WidgetImage
     *
     */
    public function testNewWidget()
    {

        $widgetImageManagerMock = $this->getWidgetImageManagerMock();
        $pageMock = $this->getPageMock();
        $templateMock = $this->getTemplateMock();

        $response = $widgetImageManagerMock->newWidget($pageMock, "3cols");
        $this->assertEquals('Victoire\Widget\ImageBundle\Entity\WidgetImage', get_class($response));

        $response = $widgetImageManagerMock->newWidget($templateMock, "3cols");
        $this->assertEquals('Victoire\Widget\ImageBundle\Entity\WidgetImage', get_class($response));

    }

    /**
     * render the WidgetImage
     */
    public function testrender()
    {

        $widgetImageManagerMock = $this->getWidgetImageManagerMock();
        $widgetMock = $this->getWidgetImageMock();

        $response = $widgetImageManagerMock->render($widgetMock);
        $html = $this->container->get('victoire_templating')->render(
            "VictoireImageBundle:show.html.twig",
            array(
                "widget" => $widgetMock
            )
        );

        $this->assertEquals($html, $response);
    }

    /**
     * create a form with given widget
     */
    public function testBuildForm()
    {
        $widgetImageManagerMock = $this->getWidgetImageManagerMock();
        $widgetMock = $this->getWidgetImageMock();

        $response = $widgetImageManagerMock->buildForm($widgetMock);
        $this->assertEquals('Symfony\Component\Form\Form', get_class($response));

    }

}
