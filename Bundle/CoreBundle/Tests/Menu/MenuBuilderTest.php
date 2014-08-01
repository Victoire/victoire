<?php
namespace Victoire\Bundle\CoreBundle\Tests\Menu;

use Victoire\Bundle\CoreBundle\Tests\Utils\BaseTestCase;

/**
 * Tests the CmsExtension class
 */
class MenuBuilderTest extends BaseTestCase
{

    /**
     * create an admin menu defined in the contructor
     */
    public function testCreateAdminMenu()
    {
        $menuBuilderMock = $this->getMenuBuilderMock();
        $response = $menuBuilderMock->createAdminMenu();

        $this->assertEquals("Knp\Menu\MenuItem", get_class($response));
    }

    /**
     * {@inheritDoc}
     */
    public function testCreateDropdownMenuItem()
    {
        $menuBuilderMock = $this->getMenuBuilderMock();
        $item = $menuBuilderMock->createAdminMenu();

        $response = $menuBuilderMock->createDropdownMenuItem($item, "test");
        $this->assertEquals("test", $response->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function testAddCaret()
    {    $menuBuilderMock = $this->getMenuBuilderMock();
        $item = $menuBuilderMock->createAdminMenu();
        $response = $menuBuilderMock->addCaret($item, array());

        $this->assertEquals("Knp\Menu\MenuItem", get_class($response));
    }

    /**
     * return menu
     */
    public function testGetMenu()
    {
        $menuBuilderMock = $this->getMenuBuilderMock();
        $response = $menuBuilderMock->createAdminMenu();

        $this->assertEquals("Knp\Menu\MenuItem", get_class($response));
    }
}
