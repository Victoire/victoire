<?php
namespace Victoire\Bundle\CoreBundle\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * common behavior for testing
 */
class BaseTestCase extends WebTestCase
{

    /**
     * @var \AppKernel
     */
    protected static $kernel;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @return null
     */
    public function setUp()
    {
        static::$kernel = parent::createKernel();
        // $this->kernel = new AppKernel('test', true);
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();
        // print_r(get_class($this->container));exit;
        $this->entityManager = $this->container->get('doctrine')->getManager();

        // $this->generateSchema();

        parent::setUp();
    }

    /**
     * @return null
     */
    public function tearDown()
    {
        static::$kernel->shutdown();

        parent::tearDown();
    }

    //////////////////////////
    // Menu listeners Mocks //
    //////////////////////////

    /**
     * Generates a TemplateMenuListener mock
     * @return TemplateMenuListener
     */
    public function getTemplateMenuListenerMock()
    {
        $factory = $this->container->get("knp_menu.factory");
        $security = $this->container->get("security.context");

        $menuBuilder = new \Victoire\Bundle\CoreBundle\Menu\MenuBuilder($factory, $security);

        return new \Victoire\Bundle\CoreBundle\Listener\TemplateMenuListener($menuBuilder);
    }
    /**
     * Generates a PageMenuListener mock
     * @return PageMenuListener
     */
    public function getPageMenuListenerMock()
    {
        $factory = $this->container->get("knp_menu.factory");
        $security = $this->container->get("security.context");

        $menuBuilder = new \Victoire\Bundle\CoreBundle\Menu\MenuBuilder($factory, $security);

        return new \Victoire\Bundle\CoreBundle\Listener\PageMenuListener($menuBuilder);
    }
    /**
     * Generates a SiteMapMenuListener mock
     * @return SiteMapMenuListener
     */
    public function getSiteMapMenuListenerMock()
    {
        $factory = $this->container->get("knp_menu.factory");
        $security = $this->container->get("security.context");

        $menuBuilder = new \Victoire\Bundle\CoreBundle\Menu\MenuBuilder($factory, $security);

        return new \Victoire\Bundle\CoreBundle\Listener\SiteMapMenuListener($menuBuilder);
    }
    ////////////////////////
    // TemplateMapper Mocks //
    ////////////////////////

    /**
     * Generates a TemplateMapper mock
     * @return MenuBuilder
     */
    public function getTemplateMapperMock()
    {
        $templating = $this->container->get("templating");
        $framework = $this->container->getParameter("victoire_core.framework");
        $bundle = $this->container->getParameter("victoire_core.applicative_bundle");

        return new \Victoire\Bundle\CoreBundle\Template\TemplateMapper($this->container);
    }
    ////////////////////////
    // MenuBuilder Mocks //
    ////////////////////////

    /**
     * Generates a MenuBuilder mock
     * @return MenuBuilder
     */
    public function getMenuBuilderMock()
    {
        $factory = $this->container->get("knp_menu.factory");
        $security = $this->container->get("security.context");

        return new \Victoire\Bundle\CoreBundle\Menu\MenuBuilder($factory, $security);
    }

    ////////////////////////
    // CmsExtension Mocks //
    ////////////////////////

    /**
     * Generates a WidgetManager mock
     * @return WidgetManager
     */
    public function getCmsExtensionMock()
    {
        $slots = $this->container->getParameter("victoire_core.slots");
        $slots = $this->container->getParameter("victoire_core.slots");
        $menuManager = $this->getMenuManagerMock();
        $widgetManager = $this->getWidgetManagerMock();
        $templateMapper = $this->getTemplateMapperMock();

        return new \Victoire\Bundle\CoreBundle\Twig\Extension\CmsExtension($menuManager, $widgetManager, $templateMapper);
    }

    //////////////////
    // Widget Mocks //
    //////////////////

    /**
     * Generates a WidgetManager mock
     * @return WidgetManager
     */
    public function getWidgetManagerMock()
    {
        return new \Victoire\Bundle\CoreBundle\Widget\Managers\WidgetManager($this->container);
    }
    /**
     * Generates a WidgetManager mock
     * @return WidgetManager
     */
    public function getMenuManagerMock()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $twig = $this->container->get('twig');
        $router = $this->container->get('router');

        return new \Victoire\Bundle\CoreBundle\Menu\MenuManager($em, $twig, $router);
    }
    /**
     * Generates a WidgetTextManager mock
     * @return WidgetManager
     */
    public function getWidgetTextManagerMock()
    {
        return new \Victoire\TextBundle\Widget\Manager\WidgetTextManager($this->container);
    }
    /**
     * Generates a WidgetImageManager mock
     * @return WidgetManager
     */
    public function getWidgetImageManagerMock()
    {
        return new \Victoire\ImageBundle\Widget\Manager\WidgetImageManager($this->container);
    }

    /**
     * Generates a Page mock
     * @param int $id page id
     *
     * @return Page
     */
    public function getPageMock($id = 1)
    {
        $page = new \Victoire\Bundle\PageBundle\Entity\Page();
        $page->setId($id);
        $page->setTitle("test");
        $page->setSlug("test");

        return $page;
    }
    /**
     * Generates a Template mock
     * @param int $id Template id
     *
     * @return Template
     */
    public function getTemplateMock($id = 1)
    {
        $template = new \Victoire\Bundle\PageBundle\Entity\Template();
        $template->setId($id);
        $template->setTitle("test");
        $template->setSlug("test");
        $template->setLayout("3cols");

        return $template;
    }

    /**
     * Generates a WidgetText mock
     * @param int $id widget id
     *
     * @return WidgetText
     */
    public function getWidgetMock($id = 1)
    {
        $widget = new \Victoire\TextBundle\Entity\WidgetText();
        $widget->setId($id);

        return $widget;
    }
    /**
     * Generates a WidgetImage mock
     * @param int $id widget id
     *
     * @return WidgetText
     */
    public function getWidgetImageMock($id = 1)
    {
        $widget = new \Victoire\Widget\ImageBundle\Entity\WidgetImage();
        $image = new \Victoire\Bundle\MediaBundle\Entity\Media();
        $widget->setImage($image);
        $widget->setId($id);

        return $widget;
    }

}
