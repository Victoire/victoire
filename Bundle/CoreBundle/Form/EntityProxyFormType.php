<?php

namespace Victoire\Bundle\CoreBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;

/**
 * Create an entity proxy for the widget.
 *
 * @author Paul Andrieux
 */
class EntityProxyFormType extends AbstractType
{
    /** @var RequestStack */
    private $requestStack;

    /**
     * EntityProxyFormType constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
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
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        //add the link to the business entity instance
        //it depends of the form
        $builder
            ->add($options['business_entity_id'], EntityType::class, [
                'label'       => false,
                'required'    => false,
                'placeholder' => 'entity_proxy.form.empty_value',
                'class'       => $options['namespace'],
                'attr'        => [
                    'class' => 'add_'.$options['business_entity_id'].'_link picker_entity_select',
                ],
                'query_builder' => function (EntityRepository $er) use ($options, $locale) {
                    // Don't display entities that don't have translations in the current locale.
                    if (in_array(Translatable::class, class_uses($options['namespace']))) {
                        return $er->createQueryBuilder('entity')
                            ->join('entity.translations', 't')
                            ->andWhere('t.locale = :s')
                            ->setParameter(':s', $locale);
                    }

                    return $er->createQueryBuilder('entity');
                },
            ]);
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
            'translation_domain' => 'victoire',
        ]);
    }
}
