<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\WidgetBundle\Model\Widget;

/**
 * Create an entity proxy for the widget.
 *
 * @author Paul Andrieux
 */
class EntityProxyFormType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $this->entityManager;

        if ($options['mode'] === Widget::MODE_ENTITY) {
            $builder->add('ressourceId', EntityType::class, [
                'label'       => false,
                'required'    => false,
                'choice_value'    => 'id',
                'placeholder' => 'entity_proxy.form.empty_value',
                'class'       => $options['namespace'],
                'attr'        => [
                    'class' => 'add_'.$options['business_entity_id'].'_link picker_entity_select',
                ],
            ]);

            $builder->get('ressourceId')->addModelTransformer(new CallbackTransformer(
                function ($idToEntity) use ($entityManager, $options) {
                    // transform the array to a string
                    return $entityManager->getRepository($options['namespace'])->findOneById($idToEntity);
                },
                function ($entityToId) {
                    // transform the string back to an array
                    return $entityToId->getId();
                }
            ));
        }
        $builder->add('businessEntity', HiddenType::class, [
            'data' => $options['business_entity_id']
        ]);

        $builder->get('businessEntity')->addModelTransformer(new CallbackTransformer(
                function ($businessEntity) {
                    return $businessEntity;
                },
                function ($nameToBusinessEntity) use ($entityManager, $options) {
                    return $entityManager->getRepository('VictoireBusinessEntityBundle:BusinessEntity')->findOneByName($nameToBusinessEntity);
                }
            ));
    }

    /**
     * bind to Menu entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\CoreBundle\Entity\EntityProxy',
            'business_entity_id' => null,
            'namespace'          => null,
            'widget'             => null,
            'mode'               => null,
            'translation_domain' => 'victoire',
        ]);
    }
}
