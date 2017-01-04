<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
use Victoire\Bundle\APIBusinessEntityBundle\Resolver\APIBusinessEntityResolver;
use Victoire\Bundle\BusinessEntityBundle\Entity\BusinessEntity;
use Victoire\Bundle\CoreBundle\Form\Field\APISelect2Type;
use Victoire\Bundle\ORMBusinessEntityBundle\Entity\ORMBusinessEntity;
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
    /**
     * @var APIBusinessEntityResolver
     */
    private $apiBusinessEntityResolver;

    public function __construct(EntityManager $entityManager, APIBusinessEntityResolver $apiBusinessEntityResolver)
    {
        $this->entityManager = $entityManager;
        $this->apiBusinessEntityResolver = $apiBusinessEntityResolver;
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
            $businessEntity = $entityManager->getRepository(
                'VictoireBusinessEntityBundle:BusinessEntity'
            )->findOneByName($options['business_entity_id']);
            if ($businessEntity->getType() == ORMBusinessEntity::TYPE) {
                $builder->add(
                    'ressourceId',
                    EntityType::class,
                    [
                        'label'        => false,
                        'required'     => false,
                        'choice_value' => 'id',
                        'placeholder'  => 'entity_proxy.form.empty_value',
                        'class'        => $businessEntity->getClass(),
                        'attr'         => [
                            'class' => 'add_'.$options['business_entity_id'].'_link picker_entity_select',
                        ],
                    ]
                );

                $builder->get('ressourceId')->addModelTransformer(
                    new CallbackTransformer(
                        function ($idToEntity) use ($entityManager, $businessEntity) {
                            // transform the array to a string
                            return $entityManager->getRepository($businessEntity->getClass())->findOneById($idToEntity);
                        },
                        function ($entityToId) {
                            // transform the string back to an array
                            return $entityToId->getId();
                        }
                    )
                );
            } else {
                /** @var APIBusinessEntity $businessEntity */
                if ($businessEntity->getListMethod()) {
                    $builder->add(
                        'ressource_id',
                        APISelect2Type::class,
                        [
                            'businessEntity'    => $businessEntity,
                            'label'             => false,
                            'required'          => false,
                            'attr'              => [
                                'class' => 'add_'.$options['business_entity_id'].'_link picker_entity_select',
                            ],
                        ]
                    );
                } else {
                    $builder->add(
                        'ressource_id',
                        TextType::class,
                        [
                            'label'    => false,
                            'required' => false,
                            'attr'     => [
                                'class' => 'add_'.$options['business_entity_id'].'_link picker_entity_select',
                            ],
                        ]
                    );
                }

                $builder->add(
                    'additionnalProperties',
                    AdditionnalPropertiesType::class,
                    [
                        'businessEntity' => $businessEntity,
                    ]
                );
            }

            $builder->add(
                'businessEntity',
                HiddenType::class,
                [
                    'data' => $options['business_entity_id'],
                ]
            );

            $builder->get('businessEntity')->addModelTransformer(
                new CallbackTransformer(
                    function ($businessEntity) {
                        return $businessEntity;
                    },
                    function ($nameToBusinessEntity) use ($entityManager) {
                        return $entityManager->getRepository(
                            'VictoireBusinessEntityBundle:BusinessEntity'
                        )->findOneByName(
                            $nameToBusinessEntity
                        );
                    }
                )
            );
        }
    }

    /**
     * bind to Menu entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(
        OptionsResolver $resolver
    ) {
        $resolver->setDefaults(
            [
                'data_class'         => 'Victoire\Bundle\CoreBundle\Entity\EntityProxy',
                'business_entity_id' => null,
                'namespace'          => null,
                'widget'             => null,
                'mode'               => null,
                'translation_domain' => 'victoire',
            ]
        );
    }
}
