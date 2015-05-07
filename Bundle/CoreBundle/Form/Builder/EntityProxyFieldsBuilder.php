<?php

namespace Victoire\Bundle\CoreBundle\Form\Builder;

use Symfony\Component\Translation\TranslatorInterface;
use Victoire\Bundle\CoreBundle\Annotations\Reader\AnnotationReader;

/**
 * Edit Page Type
 * @author lenybernard
 */
class EntityProxyFieldsBuilder
{
    private $annotationReader;
    private $translator;

    /**
     * define form fields
     */
    public function __construct(AnnotationReader $annotationReader, TranslatorInterface $translator)
    {
        $this->annotationReader = $annotationReader;
        $this->translator = $translator;
    }

    /**
     * Build
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param string                                       $namespace
     *
     * @return array The all list of fields type to add for the entity namespace given
     */
    public function buildForEntityAndWidgetType(&$builder, $widgetType, $namespace)
    {
        //Try to add a new form for each entity with the correct annotation and business properties
        $businessProperties = $this->annotationReader->getBusinessProperties($namespace);
        $receiverProperties = $this->annotationReader->getReceiverProperties();

        if (!empty($receiverProperties[$widgetType])) {
            foreach ($receiverProperties[$widgetType] as $key => $_fields) {
                foreach ($_fields as $fieldKey => $fieldVal) {
                    //Check if entity has all the required receiver properties as business properties
                    if (isset($businessProperties[$key]) && is_array($businessProperties[$key]) && count($businessProperties[$key])) {
                        //Create form types with field as key and values as choices
                        //TODO Add some formatter Class or a buildField method responsible to create this type
                        $label = $this->translator->trans('widget_'.strtolower($widgetType).'.form.'.$fieldKey.'.label', array(), 'victoire');
                        $builder->add($fieldKey, 'choice', array(
                                'choices' => $businessProperties[$key],
                                'label' => $label,
                                'attr' => array(
                                    'title' => $label
                                )
                        ));
                    } else {
                        throw new \Exception(sprintf('The Entity %s doesn\'t have a %s property, which is required by %s widget', $namespace, $key, $widgetType));
                    }
                }
            }
        }
    }
}
