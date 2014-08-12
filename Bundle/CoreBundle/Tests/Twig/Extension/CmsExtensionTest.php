<?php
namespace Victoire\Bundle\CoreBundle\Tests\Twig\Extension;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;

/**
 * Tests the CmsExtension class
 */
class CmsExtensionTest extends BaseTestCase
{

    public function testCmsWidgetActions()
    {
        $cmsExtentionMock = $this->getCmsExtensionMock();
        $widgetMock = $this->getWidgetMock();
        $response = $cmsExtentionMock->cmsWidgetActions($widgetMock);
        $this->assertTrue(is_string($response));
    }

    public function testCmsSlotWidgets()
    {
        $cmsExtentionMock = $this->getCmsExtensionMock();
        $pageMock = $this->getPageMock();
        $response = $cmsExtentionMock->cmsSlotWidgets($pageMock, '3cols_left');
        $this->assertTrue(is_string($response));
    }
    public function testCmsWidget()
    {
        $cmsExtentionMock = $this->getCmsExtensionMock();
        $widgetMock = $this->getWidgetMock();
        $response = $cmsExtentionMock->cmsWidget($widgetMock);
        $this->assertTrue(is_string($response));
    }

    public function testCmsPage()
    {
        $cmsExtentionMock = $this->getCmsExtensionMock();
        $pageMock = $this->getPageMock();
        $templateMock = $this->getPageMock();
        $pageMock->setTemplate($templateMock);
        $response = $cmsExtentionMock->cmsPage($pageMock);
        $this->assertTrue(is_string($response));
    }

}
