<?php
namespace Victoire\Bundle\CoreBundle\Tests\Widget\Managers;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;

/**
 * Tests the WidgetTextManager class
 */
class WidgetTextManagerTest extends BaseTestCase
{

    /**
     * create a new WidgetText
     *
     */
    public function testNewWidget()
    {

        $widgetTextManagerMock = $this->getWidgetTextManagerMock();
        $pageMock = $this->getPageMock();
        $templateMock = $this->getTemplateMock();

        $response = $widgetTextManagerMock->newWidget($pageMock, "3cols");
        $this->assertEquals('Victoire\TextBundle\Entity\WidgetText', get_class($response));

        $response = $widgetTextManagerMock->newWidget($templateMock, "3cols");
        $this->assertEquals('Victoire\TextBundle\Entity\WidgetText', get_class($response));

    }

    /**
     * render the WidgetText
     */
    public function testrender()
    {

        $widgetTextManagerMock = $this->getWidgetTextManagerMock();
        $widgetMock = $this->getWidgetMock();

        $response = $widgetTextManagerMock->render($widgetMock);
        $html = $this->container->get('victoire_templating')->render(
            "VictoireTextBundle:show.html.twig",
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
        $widgetTextManagerMock = $this->getWidgetTextManagerMock();
        $widgetMock = $this->getWidgetMock();

        $response = $widgetTextManagerMock->buildForm($widgetMock);
        $this->assertEquals('Symfony\Component\Form\Form', get_class($response));

    }

    /**
     * render WidgetText form
     */
    public function testRenderForm()
    {
        $widgetTextManagerMock = $this->getWidgetTextManagerMock();
        $widgetMock = $this->getWidgetMock();

        $form = $widgetTextManagerMock->buildForm($widgetMock);
        $response = $widgetTextManagerMock->renderForm($form, $widgetMock);

        $html = $this->container->get('victoire_templating')->render(
            "VictoireTextBundle:edit.html.twig",
            array(
                'widget' => $widgetMock,
                'id'     => $widgetMock->getId(),
                'form'   => $form->createView(),
                'entity' => null
            ));

        $this->assertEquals($html, $response);

    }

    /**
     * create form new for WidgetText
     */
    public function testRenderNewForm()
    {
        $widgetTextManagerMock = $this->getWidgetTextManagerMock();
        $widgetMock = $this->getWidgetMock();
        $pageMock = $this->getPageMock();

        $form = $widgetTextManagerMock->buildForm($widgetMock);
        $formView = $widgetTextManagerMock->renderForm($form, $widgetMock);
        $response = $widgetTextManagerMock->renderNewForm($form, $widgetMock, "3cols_left", $pageMock);

        $html = $this->container->get('victoire_templating')->render(
            "VictoireTextBundle:new.html.twig",
            array(
                "widget" => $widgetMock,
                'form'   => $form->createView(),
                "slot"   => '3cols_left',
                "page"   => $pageMock,
                "entity" => null
            )
        );

        $this->assertEquals($html, $response);

    }
}
