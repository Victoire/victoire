<?php
namespace Victoire\Bundle\CoreBundle\Tests\Widget\Managers;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;

/**
 * Tests the WidgetManager class
 */
class WidgetManagerTest extends BaseTestCase
{

    /**
     * Tests if this method acts correctly
     * @return void
     */
    public function testPopulateChildrenReferencesWidget()
    {
        $widgetManagerMock = $this->getWidgetManagerMock();
        $pageMock = $this->getPageMock();
        $templateMock = $this->getTemplateMock();
        $widgetMock = $this->getWidgetMock();

        $widgetManagerMock->populateChildrenReferences($pageMock, $widgetMock);

        $widgetManagerMock->populateChildrenReferences($templateMock, $widgetMock);

    }
    /**
     * Tests if this render method return a string
     * @return void
     */
    public function testRender()
    {
        $widgetManagerMock = $this->getWidgetManagerMock();
        $widgetMock = $this->getWidgetMock();
        $response = $widgetManagerMock->render($widgetMock);

        $this->assertTrue(is_string($response));
    }


    /**
     * Tests if this renderWidgetActions method return espected template
     * @return void
     */
    public function testRenderWidgetActions()
    {
        $widgetManagerMock = $this->getWidgetManagerMock();
        $widgetMock = $this->getWidgetMock();

        $response = $widgetManagerMock->renderWidgetActions($widgetMock);
        $this->assertFalse($widgetManagerMock->isReference($widgetMock));
        $html = $this->container->get('victoire_templating')->render(
            "VictoireCoreBundle:Widget:widgetActions.html.twig",
            array("widget" => $widgetMock)
        );
        $this->assertEquals($html, $response);

    }

    /**
     * Tests if this renderActions method return a string
     * @return void
     */
    public function testRenderActions()
    {
        $widgetManagerMock = $this->getWidgetManagerMock();
        $pageMock = $this->getPageMock();

        $response = $widgetManagerMock->renderActions('3cols_left', $pageMock);

        $this->assertTrue(is_string($response));
    }

    /**
     * Tests if this getManager method return espected manager or throws a exception
     * @return void
     */
    public function testGetManager()
    {
        $widgetManagerMock = $this->getWidgetManagerMock();

        $widget = new \Victoire\TextBundle\Entity\WidgetText();
        $response = $widgetManagerMock->getManager($widget);
        $this->assertEquals('Victoire\TextBundle\Widget\Manager\WidgetTextManager', get_class($response));

        $widget = new \Victoire\ImageBundle\Entity\WidgetImage();
        $response = $widgetManagerMock->getManager($widget);
        $this->assertEquals('Victoire\ImageBundle\Widget\Manager\WidgetImageManager', get_class($response));

        $this->setExpectedException('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');
        $response = $widgetManagerMock->getManager(null);

        $response = $widgetManagerMock->getManager(null, 'text');
        $this->assertEquals('Victoire\TextBundle\Widget\Manager\WidgetTextManager', get_class($response));

        $response = $widgetManagerMock->getManager(null, 'image');
        $this->assertEquals('Victoire\ImageBundle\Widget\Manager\WidgetImageManager', get_class($response));

        $this->setExpectedException('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');
        $response = $widgetManagerMock->getManager(null, 'foo');
    }





}
