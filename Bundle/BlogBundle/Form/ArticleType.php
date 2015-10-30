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
class ArticleType extends AbstractType
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
        $viewToIdTransformer = new ViewToIdTransformer($this->entityManager);

        $builder
            ->add('name', null, [
                'label' => 'form.article.name.label',
            ])
            ->add('description', null, [
                'label'    => 'form.article.description.label',
                'required' => false,
            ])
            ->add('image', 'media', [
                'required' => false,
                'label'    => 'form.article.image.label',
            ])
            ->add($builder
                ->create('blog', 'hidden', ['label' => 'form.article.blog.label'])
                ->addModelTransformer($viewToIdTransformer))
            ->add('pattern')
            ->add('tags', 'tags', [
                'required' => false,
                'multiple' => true,
            ])
            ->remove('visibleOnFront');

        $builder->get('blog')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $parent = $event->getForm()->getParent();
            $this->manageCategories($data, $parent);
            $this->manageTemplate($data, $parent);
        });

        $builder->get('blog')->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $parent = $event->getForm()->getParent();
            $this->manageCategories($data, $parent);
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $this->manageTags($data, $form);
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $form->getData();
            $this->manageTags($data, $form);
        });
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     */
    protected function manageTags($data, $form)
    {
        $form->add('tags', 'tags', [
            'required'      => false,
            'multiple'      => true,
            'query_builder' => function (TagRepository $er) use ($data) {
                $qb = $er->filterByBlog($data->getBlog())->getInstance();
                $er->clearInstance();

                return $qb;
            },
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface|null $form
     */
    protected function manageCategories($blogId, $form)
    {
        $categoryRepo = $this->entityManager->getRepository('Victoire\Bundle\BlogBundle\Entity\Category');

        if ($blogId) {
            $queryBuilder = $categoryRepo->getOrderedCategories($blogId)->getInstance();
            $categoryRepo->clearInstance();
        } else {
            $queryBuilder = $categoryRepo->getAll()->getInstance();
            $categoryRepo->clearInstance();
        }

        $form->add('category', 'hierarchy_tree', [
            'required'      => false,
            'label'         => 'form.article.category.label',
            'class'         => 'Victoire\\Bundle\\BlogBundle\\Entity\\Category',
            'query_builder' => $queryBuilder,
            'empty_value'   => 'Pas de catégorie',
            'empty_data'    => null,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface|null $form
     */
    protected function manageTemplate($blog_id, $form)
    {
        $articleTemplateRepo = $this->entityManager->getRepository('VictoireBlogBundle:ArticleTemplate');

        if ($articleTemplateRepo->filterByBlog($blog_id)->getCount('parent')->run('getSingleScalarResult') > 1) {
            $articlePatterns = function (EntityRepository $repo) use ($blog_id) {
                return $repo->filterByBlog($blog_id)->getInstance();
            };
            $form->add('pattern', null, [
                'label'         => 'form.view.type.pattern.label',
                'property'      => 'name',
                'required'      => true,
                'query_builder' => $articlePatterns,
            ]);
        } else {
            $form->add('pattern', 'victoire_article_template_type', [
                'data_class' => null,
                'data'       => $articleTemplateRepo->filterByBlog($blog_id)->run('getSingleResult'),
            ]);
        }
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
        return 'victoire_article_type';
    }
}
