<?php

namespace Victoire\Bundle\BlogBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Repository\ArticleTemplateRepository;
use Victoire\Bundle\BlogBundle\Repository\TagRepository;
use Victoire\Bundle\CoreBundle\DataTransformer\ViewToIdTransformer;
use Victoire\Bundle\MediaBundle\Form\Type\MediaType;

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
            ->add($builder
                ->create('blog', HiddenType::class, ['label' => 'form.article.blog.label'])
                ->addModelTransformer($viewToIdTransformer))
            ->add('template')
            ->add('tags', TagsType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->remove('visibleOnFront');

        $builder->get('blog')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $parent = $event->getForm()->getParent();
            $this->manageCategories($data, $parent);
            $this->manageTemplate($data, $parent);
            $this->manageLocales($data, $parent);
        });

        $builder->get('blog')->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $parent = $event->getForm()->getParent();
            $this->manageCategories($data, $parent);
            $this->manageLocales($data, $parent);
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
        $form->add('tags', TagsType::class, [
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
     *
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     */
    protected function manageLocales($blog, $form)
    {
        if (!$blog instanceof Blog) {
            $blog = $this->entityManager->getRepository('VictoireBlogBundle:Blog')->findOneById($blog);
        }
        $translations = $blog->getTranslations();
        $availableLocales = [];
        foreach ($translations as $translation) {
            $availableLocales[] = $translation->getLocale();
        }
        $form->add('translations', TranslationsType::class, [
            'required_locales' => $availableLocales,
            'locales'          => $availableLocales,
            'fields'           => [
                'name' => [
                    'label' => 'form.article.name.label',
                ],
                'image' => [
                    'label'      => 'form.article.image.label',
                    'field_type' => MediaType::class,
                    'required'   => false,
                ],

            ],
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

        $form->add('category', HierarchyTreeType::class, [
            'required'      => false,
            'label'         => 'form.article.category.label',
            'class'         => 'Victoire\\Bundle\\BlogBundle\\Entity\\Category',
            'query_builder' => $queryBuilder,
            'placeholder'   => 'form.article.category.placeholder',
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface|null $form
     */
    protected function manageTemplate($blog_id, $form)
    {
        $articleTemplateRepo = $this->entityManager->getRepository('VictoireBlogBundle:ArticleTemplate');

        if (!$form->getData()->getTemplate()) {
            if ($articleTemplateRepo->filterByBlog($blog_id)->getCount('parent')->run('getSingleScalarResult') > 1) {
                $articleTemplates = function (ArticleTemplateRepository $repo) use ($blog_id) {
                    return $repo->filterByBlog($blog_id)->getInstance();
                };
                $form->add('template', null, [
                    'label'         => 'form.article.type.template.label',
                    'property'      => 'backendName',
                    'required'      => true,
                    'query_builder' => $articleTemplates,
                ]);
            } else {
                $form->add('template', ArticleTemplateType::class, [
                    'data_class' => null,
                    'data'       => $articleTemplateRepo->filterByBlog($blog_id)->run('getSingleResult'),
                ]);
            }
        } else {
            $form->remove('template');
        }
    }

    /**
     * bind to Page entity.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class'         => 'Victoire\Bundle\BlogBundle\Entity\Article',
                'translation_domain' => 'victoire',
            ]);
    }
}
