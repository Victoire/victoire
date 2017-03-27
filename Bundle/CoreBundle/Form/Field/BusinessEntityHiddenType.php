<?php

namespace Victoire\Bundle\CoreBundle\Form\Field;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessEntityHiddenType extends AbstractType
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $this->entityManager;
        parent::buildForm($builder, $options);

        $builder->addModelTransformer(
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

    /**
     * bind form to WidgetRedactor entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'victoire',
        ]);
    }
}
