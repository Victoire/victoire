<?php

namespace Victoire\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty;
use Victoire\Bundle\BusinessEntityBundle\EventSubscriber\BusinessEntitySubscriber;
use Victoire\Bundle\CoreBundle\Annotations\BusinessEntity;
use Victoire\Bundle\CoreBundle\Annotations\BusinessProperty as BusinessPropertyAnnotation;
use Victoire\Bundle\CoreBundle\Entity\EntityProxy;
use Victoire\Bundle\CoreBundle\EventSubscriber\WidgetSubscriber;
use Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntity;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170217154603 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return null|ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->migrateBusinessEntities();
        $this->migrateEntityProxies();
        $this->migrateWidgetAndViews();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->write('Not implemented');
    }

    protected function migrateBusinessEntityName($entity)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $businessEntity = $entityManager->getRepository('VictoireORMBusinessEntityBundle:ORMBusinessEntity')
            ->createQueryBuilder('proxy')
            ->where('proxy.name LIKE :prop')
            ->setParameter(':prop', $entity->getOldBusinessEntityName())
            ->getQuery()
            ->getOneOrNullResult();

        $entity->setBusinessEntity($businessEntity);
    }

    protected function migrateOldProxies($oldProxy)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $proxy = new EntityProxy();
        foreach ($oldProxy as $oldProxyPropName => $oldProxyPropValue) {
            if ($oldProxyPropName === 'id') {
                $proxy->setId($oldProxyPropValue);
            } elseif ($oldProxyPropValue !== null) {
                $businessEntity = $entityManager->getRepository('VictoireORMBusinessEntityBundle:ORMBusinessEntity')
                    ->createQueryBuilder('proxy')
                    ->where('proxy.name LIKE :prop')
                    ->setParameter(':prop', str_replace('_id', '', $oldProxyPropName))
                    ->getQuery()
                    ->getOneOrNullResult();
                $proxy->setBusinessEntity($businessEntity);
                $proxy->setRessourceId($oldProxyPropValue);
            }
        }

        return $proxy;
    }

    public function migrateEntityProxies()
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->getContainer()->get('database_connection');
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $sql = 'SELECT * FROM vic_entity_proxy';
        $oldProxies = $conn->fetchAll($sql);

        foreach ($oldProxies as $k => $oldProxy) {
            $proxy = $this->migrateOldProxies($oldProxy);
            $entityManager->persist($proxy);
            // Force the ID
            $metadata = $entityManager->getClassMetaData(get_class($proxy));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        }

        $entityManager->flush();
    }

    public function migrateWidgetAndViews()
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->getContainer()->get('database_connection');
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($entityManager->getRepository('VictoireBusinessPageBundle:BusinessTemplate')->findAll() as $bt) {
            $this->migrateBusinessEntityName($bt);
        }
        foreach ($entityManager->getRepository('VictoireWidgetBundle:Widget')->findAll() as $widget) {
            if ($widget->getOldBusinessEntityName()) {
                $this->migrateBusinessEntityName($widget);
            }
        }

        // Do not uselessly regenerate the viewCss threw the onFlush event. It led to an exception
        $evm = $entityManager->getEventManager();
        foreach ($entityManager->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $key => $listener) {
                if ($listener instanceof WidgetSubscriber) {
                    $evm->removeEventListener(['onFlush'], $listener);
                }
                if ($listener instanceof BusinessEntitySubscriber) {
                    $evm->removeEventListener(['postUpdate'], $listener);
                }
            }
        }

        $entityManager->flush();
    }

    /**
     * Parse the given Class to find some annotations related to BusinessEntities.
     */
    public function parse(\ReflectionClass $class)
    {
        $businessEntity = null;
        $classPath = dirname($class->getFileName());
        $inPaths = false;
        foreach ($this->getContainer()->getParameter('victoire_core.base_paths') as $key => $_path) {
            //Check the entity path is in watching paths
            if (strpos($classPath, realpath($_path)) === 0) {
                $inPaths = true;
            }
        }
        if ($inPaths) {
            $classAnnotations = $this->getContainer()->get('annotation_reader')->getClassAnnotations($class);
            if (!empty($classAnnotations)) {
                foreach ($classAnnotations as $key => $annot) {
                    if (!is_numeric($key)) {
                        continue;
                    }
                    $classAnnotations[get_class($annot)] = $annot;
                }
            }

            // Evaluate Entity annotation
            if (isset($classAnnotations['Victoire\Bundle\CoreBundle\Annotations\BusinessEntity'])) {
                /** @var BusinessEntity $annotationObj */
                $annotationObj = $classAnnotations['Victoire\Bundle\CoreBundle\Annotations\BusinessEntity'];
                $businessEntity = $this->createBusinessEntity(
                    $class->getName(),
                    $annotationObj,
                    $this->loadBusinessProperties($class)
                );
            }
        }

        return $businessEntity;
    }

    /**
     * load business properties from ReflectionClass.
     *
     * @return array
     **/
    protected function loadBusinessProperties(\ReflectionClass $class)
    {
        $reader = $this->getContainer()->get('annotation_reader');
        $businessProperties = [];
        $properties = $class->getProperties();
        $traits = $class->getTraits();
        $className = $class->getName();
        // if the class is translatable, then parse annotations on it's translation class
        if (array_key_exists(Translatable::class, $traits)) {
            $translation = new \ReflectionClass($className::getTranslationEntityClass());
            $translationProperties = $translation->getProperties();
            $properties = array_merge($properties, $translationProperties);
        }

        foreach ($properties as $property) {
            $annotations = $reader->getPropertyAnnotations($property);
            foreach ($annotations as $key => $annotationObj) {
                if ($annotationObj instanceof BusinessPropertyAnnotation && !in_array($class, $businessProperties)) {
                    $businessProperties[$property->name] = $annotationObj;
                }
            }
        }
        // we load business properties of parents recursively
        // because they are defined by an annotation not by the property type(private, protected, public)
        $parentClass = $class->getParentClass();
        if ($parentClass) {
            //load parent properties recursively
            $parentProperties = $this->loadBusinessProperties(new \ReflectionClass($parentClass->getName()));
            foreach ($parentProperties as $propertyName => $parentProperty) {
                if (!array_key_exists($propertyName, $businessProperties)) {
                    $businessProperties[$propertyName] = $parentProperty;
                }
            }
        }

        return $businessProperties;
    }

    /**
     * @param string $className
     * @param BusinessEntity $annotationObj
     * @param $businessProperties
     *
     * @return ORMBusinessEntity
     */
    private function createBusinessEntity($className, $annotationObj, $businessProperties)
    {
        $businessEntity = new ORMBusinessEntity();
        $classNameArray = explode('\\', $className);
        $entityName = array_pop($classNameArray);
        $businessEntity->setName($entityName);
        $businessEntity->setClass($className);
        $businessEntity->setAvailableWidgets($annotationObj->getWidgets());
        //parse the array of the annotation reader
        foreach ($businessProperties as $propertyName => $property) {
            $businessProperty = new BusinessProperty();
            $businessProperty->setTypes($property->getTypes());
            $businessProperty->setBusinessEntity($businessEntity);
            $businessProperty->setName($propertyName);
        }

        return $businessEntity;
    }

    private function migrateBusinessEntities()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($this->getContainer()->get('victoire_business_entity.annotation_driver')->getAllClassNames() as $className) {
            $businessEntity = $this->parse(new \ReflectionClass($className));
            if ($businessEntity) {
                $entityManager->persist($businessEntity);
            }
        }

        $entityManager->flush();
    }
}
