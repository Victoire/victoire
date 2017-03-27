<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\APIBusinessEntityBundle\Entity\APIBusinessEntity;
use Victoire\Bundle\CoreBundle\Form\Field\APISelect2Type;
use Victoire\Bundle\CoreBundle\Form\Field\BusinessEntityHiddenType;
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

    /** @var RequestStack */
    private $requestStack;

    /**
     * EntityProxyFormType constructor.
     *
     * @param EntityManager $entityManager
     * @param RequestStack  $requestStack
     */
    public function __construct(EntityManager $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
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
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

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
                        'query_builder' => function (EntityRepository $er) use ($businessEntity, $locale) {
                            // Don't display entities that don't have translations in the current locale.
                            if (in_array(Translatable::class, class_uses($businessEntity->getClass()))) {
                                return $er->createQueryBuilder('entity')
                                    ->join('entity.translations', 't')
                                    ->andWhere('t.locale = :s')
                                    ->setParameter(':s', $locale);
                            }

                            return $er->createQueryBuilder('entity');
                        },
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
                        'ressourceId',
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
                    // The value comes from API, symfony will not validate it.
                    $builder->get('ressourceId')->resetViewTransformers();
                } else {
                    $builder->add(
                        'ressourceId',
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
        }

        $builder->add('businessEntity', BusinessEntityHiddenType::class, [
            'data' => $options['business_entity_id'],
        ]);
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
                'widget'             => null,
                'mode'               => null,
                'translation_domain' => 'victoire',
            ]
        );
    }
}
