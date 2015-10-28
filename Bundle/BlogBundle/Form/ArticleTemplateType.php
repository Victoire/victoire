<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\BlogBundle\Repository\TagRepository;
use Victoire\Bundle\CoreBundle\DataTransformer\ViewToIdTransformer;

/**
 *
 */
class ArticleTemplateType extends AbstractType
{
    private $entityManager;

    /**
     * Constructor.
     */
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
        $builder->add('pattern')->remove('visibleOnFront');

        $builder->get('pattern')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $articleTemplateRepo = $this->entityManager->getRepository('VictoireBlogBundle:ArticleTemplate');

            $parent = $event->getForm()->getParent()->getParent();
            $blog_id = $parent->getData()->getBlog()->getId();
            if ($articleTemplateRepo->filterByBlog($blog_id)->getCount('parent')->run('getSingleScalarResult') > 1) {
                $articlePatterns = function (EntityRepository $repo) use ($blog_id) {
                    return $repo->filterByBlog($blog_id)->getInstance();
                };
                $parent->add('pattern', null, [
                    'label'         => 'form.view.type.pattern.label',
                    'property'      => 'name',
                    'required'      => true,
                    'query_builder' => $articlePatterns,
                ]);
            } else {
                $parent->add('pattern', 'hidden', [
                    'data' => $articleTemplateRepo->filterByBlog($blog_id)->run('getSingleResult')->getId(),
                ]);
            }
        });
    }

    /**
     * bind to Page entity.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'Victoire\Bundle\BlogBundle\Entity\Article',
            'translation_domain' => 'victoire',
        ]);
    }

    /**
     * get form name.
     *
     * @return string The name of the form
     */
    public function getName()
    {
        return 'victoire_article_template_type';
    }
}
