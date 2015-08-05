<?php

namespace Victoire\Bundle\CoreBundle\Form\Builder;

use Victoire\Bundle\BusinessEntityBundle\Reader\BusinessEntityCacheReader;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormRegistryInterface;

/**
 * Edit Page Type
 * @author lenybernard
 */
class EntityProxyFieldsBuilder
{
    private $cacheReader;
    private $translator;
    private $registry;
    private $widgets = array();

    /**
     * define form fields
     */
    public function __construct(BusinessEntityCacheReader $cacheReader, TranslatorInterface $translator, FormRegistryInterface $registry, $widgets)
    {
        $this->cacheReader = $cacheReader;
        $this->translator = $translator;
        $this->registry = $registry;
        $this->widgets = $widgets;
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
        $businessProperties = $this->cacheReader->getBusinessProperties($namespace);
        $receiverProperties = $this->cacheReader->getReceiverProperties();

        if (!empty($receiverProperties[$widgetType])) {
            foreach ($receiverProperties[$widgetType] as $key => $_fields) {
                foreach ($_fields as $fieldKey => $fieldVal) {
                    //Check if entity has all the required receiver properties as business properties
                    if (isset($businessProperties[$key]) && is_array($businessProperties[$key]) && count($businessProperties[$key])) {
                        //Create form types with field as key and values as choices
                        //TODO Add some formatter Class or a buildField method responsible to create this type
                        $label = $this->translator->trans('widget_'.strtolower($widgetType).'.form.'.$fieldKey.'.label', array(), 'victoire');
                        $options = array(
                                'choices' => $businessProperties[$key],
                                'label' => $label,
                                'attr' => array(
                                    'title' => $label
                                )
                        );
                        //GuessRequire for each property
                        if (array_key_exists($widgetType, $this->widgets)) {
                            $widgetClass = $this->widgets[$widgetType]['class'];
                            $guesser = $this->registry->getTypeGuesser();
                            $requiredGuess = $guesser->guessRequired($widgetClass, $fieldKey);
                            if ($requiredGuess) {
                                $options = array_merge(array('required' => $requiredGuess->getValue()), $options);
                            }
                        }

                        $builder->add($fieldKey, 'choice', $options);
                    } else {
                        throw new \Exception(sprintf('The Entity %s doesn\'t have a %s property, which is required by %s widget', $namespace, $key, $widgetType));
                    }
                }
            }
        }
    }
}
