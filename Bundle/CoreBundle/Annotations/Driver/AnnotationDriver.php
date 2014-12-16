<?php
namespace Victoire\Bundle\CoreBundle\Annotations\Driver;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver as AbstractAnnotationDriver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Victoire\Bundle\CoreBundle\Annotations\BusinessEntity;
use Victoire\Bundle\CoreBundle\Annotations\BusinessProperty;
use Victoire\Bundle\CoreBundle\Annotations\ReceiverProperty;
use Victoire\Bundle\WidgetBundle\Entity\Widget;

/**
 * The annotation reader for the business entities
 *
 * ref: victoire_core.annotation_driver
 **/
class AnnotationDriver extends AbstractAnnotationDriver
{
    /**
     * construct
     * @param AnnotationReader         $reader
     * @param EventDispatcherInterface $eventDispatcher
     * @param array                    $paths           The paths where to search about Entities
     */
    public function __construct(Reader $reader, EventDispatcherInterface $eventDispatcher, $paths)
    {
        $this->reader = $reader;
        $this->eventDispatcher = $eventDispatcher;
        $this->addPaths($paths);
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadataInfo */
        $class = $metadata->getReflectionClass();
        if (! $class) {
            $class = new \ReflectionClass($metadata->name);
        }

        $classAnnotations = $this->reader->getClassAnnotations($class);

        if ($classAnnotations) {
            foreach ($classAnnotations as $key => $annot) {
                if ( ! is_numeric($key)) {
                    continue;
                }

                $classAnnotations[get_class($annot)] = $annot;
            }
        }

        // Evaluate Entity annotation
        if (isset($classAnnotations['Victoire\Bundle\CoreBundle\Annotations\BusinessEntity'])) {
            $annotationObj = $classAnnotations['Victoire\Bundle\CoreBundle\Annotations\BusinessEntity'];
            echo "annotation business entity";
            echo $className;

            $businessEntity = $this->createBusinessEntity(
                $className,
                $this->loadBusinessProperties($class)
            );

            $event = new BusinessEntityAnnotationEvent(
                $businessEntity,
                $annotationObj->getWidgets()
            );

            $this->eventDispatcher->dispatch('victoire.business_entity_annotation_load', $event);

        }

        //check if the entity is a widget (extends (in depth) widget class)
        $parentClass = $class->getParentClass();
        $isWidget = false;
        while ($parentClass = $parentClass->getParentClass() && !$isWidget && $parentClass->name != null) {
            $isWidget = $parentClass->name === 'Victoire\\Bundle\\WidgetBundle\\Model\\Widget';
        }

        if ($isWidget) {
        echo "widget entity";
        echo $className;
            $event = new WidgetAnnotationEvent(
                $className,
                $this->loadReceiverProperties()
            );

            //dispatch victoire.widget_annotation_load to save receiverProperties in cache
            $this->eventDispatcher->dispatch('victoire.widget_annotation_load', $event);
        }

    }

    private function loadBusinessProperties(\ReflectionClass $class)
    {
        $businessProperties = array();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $key => $annotationObj) {
                if ($annotationObj instanceof BusinessProperty && !in_array($class, $businessProperties)) {
                    if (!$annotations[$key]->getTypes()) {
                        $message = $class->name . ':$' . $property->name . '" field';
                        throw AnnotationException::requiredError('type', 'BusinessProperty annotation', $message, 'array or string');
                    }
                    foreach ($annotations[$key]->getTypes() as $type) {
                        $businessProperties[$type][$property->name] = $property->name;
                    }
                }
            }
        }

        return $businessProperties;

    }

    private function loadReceiverProperties(\ReflectionClass $class)
    {
        $receiverProperties = array();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $key => $annotationObj) {
                if ($annotationObj instanceof ReceiverProperty && !in_array($class, $receiverProperties)) {
                    if (!$annotations[$key]->getTypes()) {
                        $message = $class->name . ':$' . $property->name . '" field';
                        throw AnnotationException::requiredError('type', 'BusinessProperty annotation', $message, 'array or string');
                    }
                    foreach ($annotations[$key]->getTypes() as $type) {
                        $receiverProperties[$class->name][$type][$property->name] = $property->name;
                    }
                }
            }
        }

        return $receiverProperties;

    }

    /**
     * create a BusinessEntity from an annotation object
     *
     * @return BusinessEntity
     **/
    protected function createBusinessEntity($className, $businessProperties)
    {

        $businessEntity = new \Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity();

        $entityName = explode('\\', $className);
        $businessEntity->setId(strtolower(array_pop($entityName)));
        $businessEntity->setName(strtolower(array_pop($entityName)));
        $businessEntity->setClass($className);

        //parse the array of the annotation reader
        foreach ($businessProperties as $type => $properties) {
            foreach ($properties as $property) {
                $businessProperty = new \Victoire\Bundle\BusinessEntityBundle\Entity\BusinessProperty();
                $businessProperty->setType($type);
                $businessProperty->setEntityProperty($property);

                //add the business property to the business entity object
                $businessEntity->addBusinessProperty($businessProperty);
                unset($businessProperty);
            }
        }

        return $businessEntity;
    }
}
