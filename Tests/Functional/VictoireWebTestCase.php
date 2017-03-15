<?php

namespace Victoire\Tests\Functional;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\SchemaTool;
use Nelmio\Alice\Fixtures;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class VictoireWebTestCase.
 *
 * This class can be used for functional tests
 * It provides :
 *  - a property to access the container
 *  - a property to access Doctrine ManagerRegistry
 *  - a property to access the Entity Manager
 *  - a method to login into Victoire
 *  - a method to reset database schema
 *  - a method to load seeds and other fixtures
 *  - a method to reset ViewReferences
 */
class VictoireWebTestCase extends WebTestCase
{
    const VICTOIRE_FIREWALL = 'main';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * Start tests by set up Kernel, Doctrine and Entity Manager.
     */
    protected function setUp()
    {
        $this->setUpSymfonyKernel();
        $this->setUpDoctrine();
    }

    /**
     * Drop and create database schema.
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function resetSchema()
    {
        if ($metadata = $this->getMetadata()) {
            $schemaTool = new SchemaTool($this->entityManager);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }

    /**
     * Victoire login.
     *
     * @param Client $client
     * @param array  $roles
     */
    protected function logIn(Client $client, array $roles)
    {
        $session = $client->getContainer()->get('session');

        $token = new UsernamePasswordToken('test', null, self::VICTOIRE_FIREWALL, $roles);
        $session->set('_security_'.self::VICTOIRE_FIREWALL, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * Load seeds fixtures and given fixtures.
     *
     * @param array $fixtures
     */
    protected function loadFixtures(array $fixtures = [])
    {
        $fixtures = array_merge($fixtures, $this->getSeeds());
        $faker = new \Faker\Generator();
        Fixtures::load(
            $fixtures,
            $this->entityManager,
            [
                'providers' => [
                    $this,
                    new \Faker\Provider\Base($faker),
                ],
            ]
        );
        $this->resetViewsReference();
    }

    /**
     * Reset Victoire ViewReferences.
     */
    protected function resetViewsReference()
    {
        $viewsReferences = $this->container->get('victoire_core.view_helper')->buildViewsReferences();
        $this->container->get('victoire_view_reference.manager')->saveReferences($viewsReferences);
    }

    /**
     * Set up Symfony Kernel and provide container.
     */
    private function setUpSymfonyKernel()
    {
        static::$kernel = $this->createKernel();
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();
    }

    /**
     * Provide doctrine and entity manager.
     */
    private function setUpDoctrine()
    {
        $this->doctrine = $this->createDoctrineRegistry();
        $this->entityManager = $this->doctrine->getManager();
    }

    /**
     * Get seeds files for Users, Pages and Templates.
     *
     * @return array
     */
    private function getSeeds()
    {
        return [
            __DIR__ . '/../App/src/Acme/AppBundle/DataFixtures/Seeds/ORM/User/user.yml',
            __DIR__ . '/../App/src/Acme/AppBundle/DataFixtures/Seeds/ORM/View/template.yml',
            __DIR__ . '/../App/src/Acme/AppBundle/DataFixtures/Seeds/ORM/View/page.yml',
        ];
    }

    /**
     * Returns all metadata by default.
     *
     * Override to only build selected metadata.
     * Return an empty array to prevent building the schema.
     *
     * @return array
     */
    private function getMetadata()
    {
        return $this->entityManager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * Override to build doctrine registry yourself.
     *
     * By default a Symfony container is used to create it. It requires the SymfonyKernel trait.
     *
     * @return ManagerRegistry
     */
    private function createDoctrineRegistry()
    {
        if (isset(static::$kernel)) {
            return static::$kernel->getContainer()->get('doctrine');
        }

        throw new \RuntimeException(sprintf('Override %s to create a ManagerRegistry or use the SymfonyKernel trait.', __METHOD__));
    }
}
