<?php

namespace Victoire\Bundle\CoreBundle\Form\Builder;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Translation\TranslatorInterface;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessPropertyRepository;
use Victoire\Bundle\BusinessEntityBundle\Entity\ReceiverProperty;
use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;

/**
 * Edit Page Type.
 */
class EntityProxyFieldsBuilder
{
    private $translator;
    /**
     * @var EntityRepository
     */
    private $businessPropertyRepository;
    /**
     * @var EntityRepository
     */
    private $cacheReader;
    /**
     * @var array
     */
    private $widgetsConfig;

    /**
     * define form fields.
     *
     * @param EntityRepository          $businessPropertyRepository
     * @param BusinessEntityCacheReader $cacheReader
     * @param                           $widgetsConfig
     * @param TranslatorInterface       $translator
     */
    public function __construct(BusinessPropertyRepository $businessPropertyRepository, BusinessEntityCacheReader $cacheReader, $widgetsConfig, TranslatorInterface $translator)
    {
        $this->businessPropertyRepository = $businessPropertyRepository;
        $this->cacheReader = $cacheReader;
        $this->widgetsConfig = $widgetsConfig;
        $this->translator = $translator;
    }

    /**
     * Build.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param string                                       $widgetName
     * @param string                                       $namespace
     *
     * @throws \Exception
     *
     * @return array The all list of fields type to add for the entity namespace given
     */
    public function buildForEntityAndWidgetType(&$builder, $widgetName, $namespace)
    {
        //Try to add a new form for each entity with the correct annotation and business properties
        $rawBusinessProperties = $this->businessPropertyRepository->getByClassname($namespace)->run();
        $businessProperties = [];
        foreach ($rawBusinessProperties as $businessProperty) {
            foreach ($businessProperty->getTypes() as $type) {
                $businessProperties[$type][] = $businessProperty;
            }
        }
        $receiverPropertiesTypes = $this->cacheReader->getReceiverProperties($widgetName);

        if (!empty($receiverPropertiesTypes)) {
            foreach ($receiverPropertiesTypes as $type => $receiverProperties) {
                /* @var ReceiverProperty[] $receiverProperties */
                foreach ($receiverProperties as $receiverProperty) {

                    //Check if entity has all the required receiver properties as business properties
                    if (isset($businessProperties[$type]) && is_array($businessProperties[$type]) && count($businessProperties[$type])) {

                        //Create form types with field as key and values as choices
                        //TODO Add some formatter Class or a buildField method responsible to create this type
                        $label = $this->translator->trans(
                            'widget_'.strtolower($widgetName).'.form.'.$receiverProperty->getFieldName().'.label',
                            [],
                            'victoire'
                        );
                        $choices = [];
                        foreach ($businessProperties[$type] as $choice) {
                            $choices[$choice->getName()] = $choice->getName();
                        }

                        $options = [
                            'choices' => $choices,
                            'label'   => $label,
                            'attr'    => [
                                'title' => $label,
                            ],
                        ];

                        if (!$receiverProperty->isRequired()) {
                            $options = array_merge(['required' => false], $options);
                        }

                        $builder->add($receiverProperty->getFieldName(), ChoiceType::class, $options);
                    }
                }
            }
        }
    }
}
