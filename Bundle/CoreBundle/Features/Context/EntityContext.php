<?php
namespace Victoire\Bundle\CoreBundle\Features\Context;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Step,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\ScenarioEvent;

use Victoire\Bundle\PageBundle\Entity\Page;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Behat\Gherkin\Node\TableNode;

use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class EntityContext extends BehatContext implements KernelAwareInterface
{
    private $kernel;

    /**
     * Constructor
     */
    public function __construct($a)
    {
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $em = $this->getEntityManager();

        $em->getConnection();

        $em->getConnection()->executeQuery('SET foreign_key_checks=0');

        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();

        $em->getConnection()->executeQuery('SET foreign_key_checks=1');
    }

    /**
     * @param \Behat\Behat\Event\ScenarioEvent|\Behat\Behat\Event\OutlineExampleEvent $event
     *
     * @AfterScenario
     *
     * @return null
     */
    public function closeDBALConnections($event)
    {

        $this->getEntityManager()->clear();

        foreach ($this->getDoctrine()->getConnections() as $connection) {
            $connection->close();
        }
    }

    public function createPage($data)
    {

        $em = $this->getEntityManager();

        $page = $this->getPageRepository()->findOneBySlug($name);

        if (null === $page) {
            $page = new Page();
            $page->setName($name);
            $page->setSlug($name);

            $em->persist($page);
            $em->flush();
        }
    }

    /**
     * @Given /^the following pages:$/
     */
    public function theFollowingPages(TableNode $table)
    {
        $em = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $page = new Page();
            $page->setTitle($data['title']);
            $page->setSlug($data['title']);
            $em->persist($page);
        }

        $em->flush();
    }

    /**
     * @Given /^the following widgets:$/
     */
    public function theFollowingWidgets(TableNode $table)
    {
        $em = $this->getEntityManager();

        foreach ($table->getRows() as $data) {

            foreach ($data as $_data) {
                if (strpos($_data, "::") !== false ) {
                    list($field, $value) = explode("::", $_data);

                    if ($field === "type") {
                        $obj = "Victoire\\".ucfirst($value)."Bundle\Entity\Widget".ucfirst($value);
                        $widget = new $obj;
                    } else {
                        $widget->{"set".ucfirst($field)}($value);
                    }
                } elseif (strpos($_data, ":") !== false) {
                    list($field, $class, $value) = explode(":", $_data);
                    $relObj = "Victoire\Bundle\CoreBundle\Entity\\".ucfirst($class);
                    $rel = $this->getDoctrine()->getRepository($relObj)->find($value);
                    $widget->{"set".ucfirst($field)}($rel);
                }
            }
            $em->persist($widget);
        }

        $em->flush();
    }

    private function asBoolean($value)
    {
        return $value === 'yes';
    }

    public function getPageRepository()
    {
        return $this->getEntityManager()->getRepository('VictoirePageBundle:Page');
    }

    public function getWidgetRepository()
    {
        return $this->getEntityManager()->getRepository('VictoireCoreBundle:Widget');
    }

    public function refresh($entity)
    {
        $this->getDoctrine()->getManager()->refresh($entity);
    }

    private function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }

    private function getContainer()
    {
        return $this->kernel->getContainer();
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
}
