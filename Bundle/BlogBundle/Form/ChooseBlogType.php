<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Form\Type\EntityHiddenType;
use Victoire\Bundle\BlogBundle\Repository\BlogRepository;

/**
 * Choose Blog form type.
 */
class ChooseBlogType extends AbstractType
{
    protected $blogRepository;

    /**
     * ChooseBlogType constructor.
     *
     * @param BlogRepository $blogRepository
     */
    public function __construct(BlogRepository $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //Manage locale
        $availableLocales = $this->blogRepository->getUsedLocales();
        $localesNb = count($availableLocales);
        $currentLocale = $options['locale'] !== null ? $options['locale'] : ($localesNb >= 1 ? reset($availableLocales) : null);

        //Manage blog
        $availableBlogs = $localesNb > 1 ? $this->blogRepository->getBlogsForLocale($currentLocale) : $this->blogRepository->findAll();
        $blogsNb = count($availableBlogs);
        $currentBlog = $options['blog'] !== null ? $options['blog'] : ($blogsNb >= 1 ? reset($availableBlogs) : null);

        //Add fields
        $this->addLocaleField($builder, $currentLocale, $availableLocales);
        $this->addBlogField($builder, $currentBlog, $availableBlogs, $currentLocale);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($currentLocale, $currentBlog, $localesNb, $blogsNb) {
                $data = $event->getData();
                $event->setData([
                    'locale' => $data['locale'] !== null ? $data['locale'] : $currentLocale,
                    'blog'   => $currentBlog,
                ]);
            }
        );

        //Hide blog field when a locale with only one blog is selected
        if ($localesNb > 1 && $blogsNb > 1) {
            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) use ($currentLocale, $currentBlog, $blogsNb, $localesNb) {
                    $data = $event->getData();
                    $currentLocale = $data['locale'] !== null ? $data['locale'] : $currentLocale;
                    $availableBlogs = $localesNb > 1 ? $this->blogRepository->getBlogsForLocale($currentLocale) : $this->blogRepository->findAll();
                    $this->addBlogField($event->getForm(), $currentBlog, $availableBlogs, $currentLocale);
                }
            );
        }
    }

    /**
     * @param FormInterface|FormBuilderInterface $builder
     * @param string                             $currentLocale
     * @param array                              $availableLocales
     */
    public function addLocaleField($builder, $currentLocale, $availableLocales)
    {
        if (($localesNb = count($availableLocales)) < 1) {
            return;
        }

        $additionalParameters = [];
        if ($localesNb > 1) {
            $additionalParameters = [
                'choices'           => array_combine($availableLocales, $availableLocales),
                'preferred_choices' => $currentLocale,
            ];
        }

        $builder->add('locale', $localesNb > 1 ? ChoiceType::class : HiddenType::class,
            array_merge(
                [
                    'label' => 'victoire.blog.choose.locale.label',
                    'data'  => $currentLocale,
                ],
                $additionalParameters
            )
        );
    }

    /**
     * @param FormInterface|FormBuilderInterface $builder
     * @param Blog                               $currentBlog
     * @param array                              $availableBlogs
     * @param string                             $currentLocale
     */
    public function addBlogField($builder, Blog $currentBlog, $availableBlogs, $currentLocale)
    {
        if (($blogsNb = count($availableBlogs)) < 1) {
            return;
        }

        if ($blogsNb > 1) {
            $additionalParameters = [
                'choices'           => $availableBlogs,
                'choices_as_values' => true,
                'choice_value'      => function (Blog $currentBlog) {
                    return $currentBlog->getId();
                },
                'choice_label' => function (Blog $currentBlog) use ($currentLocale) {
                    $currentBlog->setCurrentLocale($currentLocale);

                    return $currentBlog->getName();
                },
            ];
        } else {
            $additionalParameters = ['class' => Blog::class];
        }

        $builder->add('blog', $blogsNb > 1 ? ChoiceType::class : EntityHiddenType::class,
            array_merge(
                [
                    'label' => 'victoire.blog.choose.blog.label',
                    'data'  => $currentBlog,
                ],
                $additionalParameters
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => null,
                'translation_domain' => 'victoire',
                'blog'               => null,
                'locale'             => null,
            ]
        );
    }
}
