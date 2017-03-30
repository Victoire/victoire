<?php

namespace Victoire\Bundle\BlogBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\BlogBundle\Entity\Blog;
use Victoire\Bundle\BlogBundle\Form\Type\EntityHiddenType;
use Victoire\Bundle\BlogBundle\Repository\BlogRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Choose Blog form type.
 */
class ChooseBlogType extends AbstractType
{
    protected $availableLocales;
    protected $blogRepository;

    public function __construct(
        array $availableLocales,
        BlogRepository $blogRepository
    ) {
        $this->blogRepository = $blogRepository;
        $this->availableLocales = $this->blogRepository->getLocalesWithBlogs();
    }
    /**
     * define form fields.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $blogs = $this->blogRepository->findAll();
        $this->blogRepository->clearInstance();
        if(count($this->availableLocales) == 1 && count($blogs) > 1)
        {
            $this->handleMultipleBlogs($builder);
        }
        if(count($this->availableLocales) > 1 && count($blogs) == 1)
        {
            $this->handleMultipleLocales($builder, $options);
        }

        if(count($this->availableLocales) > 1 && count($blogs) > 1)
        {
            $this->handleMultipleLocaleBlogs($builder, $options);
        }
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $event->stopPropagation();
        }, 900);

    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    private function handleMultipleLocaleBlogs(FormBuilderInterface $builder, array $options = array())
    {
        $blog = $options['blog'];
        $locale = $options['locale'] === null ? reset($this->availableLocales) : isset($this->availableLocales[$options['locale']])? $options['locale'] :reset($this->availableLocales);

        $builder->add('locale', ChoiceType::class, [
            'label'       => 'victoire.blog.choose.locale.label',
            'choices'     => array_combine($this->availableLocales, $this->availableLocales),
            'preferred_choices' => $locale,
            'data' => $locale
        ]);

        $formModifier = function (FormInterface $form, $currentLocale = null, Blog $blog = null) {
            $blogs = $this->blogRepository->joinTranslations($currentLocale)->getInstance()->getQuery()->getResult();
            if($blog === null)
            {
                $blog = reset($blogs);
            }
            $this->blogRepository->clearInstance();
            $form->add('blog', ChoiceType::class, [
                'label'             => 'victoire.blog.choose.blog.label',
                'choices'           => $blogs,
                'choices_as_values' => true,
                'choice_value' => function ($blog){
                    if (!$blog instanceof Blog)
                        return;
                    return $blog->getId();
                },
                'choice_label' => function ($blog) use ($currentLocale) {
                    if (!$blog instanceof Blog)
                        return;
                    $blog->setCurrentLocale($currentLocale);
                    return $blog->getName();
                },
                'data'=> $blog
            ]);

        };
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier, $locale, $blog) {
                $data = $event->getData();
                $locale = $data['locale'] !== null ? $data['locale']: $locale;
                $formModifier($event->getForm(), $locale, $blog);

                $blog = null;
                $blogs = $this->blogRepository->joinTranslations($locale)->getInstance()->getQuery()->getResult();
                if($blog === null)
                {
                    $blog = reset($blogs);
                }
                $this->blogRepository->clearInstance();
                $event->setData(
                    [
                        'locale' => $locale,
                        'blog' => $blog
                    ]
                );

            }
        );

        $builder->get('locale')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier, $blog) {
                $locale = $event->getData();
                $formModifier($event->getForm()->getParent(), $locale, $blog);
            }
        );
    }

    /**
     * @param FormBuilderInterface $builder
     */
    private function handleMultipleBlogs(FormBuilderInterface $builder)
    {

        $locale = reset($this->availableLocales);

        $blog = null;
        $blogs = $this->blogRepository->joinTranslations($locale)->getInstance()->getQuery()->getResult();
        if($blog === null)
        {
            $blog = reset($blogs);
        }
        $this->blogRepository->clearInstance();
        $builder->add('blog', ChoiceType::class, [
            'label'             => 'victoire.blog.choose.blog.label',
            'choices'           => $blogs,
            'choices_as_values' =>true,
            'choice_value' => function ($blog){
                if (!$blog instanceof Blog)
                    return;
                return $blog->getId();
            },
            'choice_label' => function ($blog) use ($locale) {
                if(!$blog instanceof Blog)
                    return;
                $blog->setCurrentLocale($locale);
                return $blog->getName();
            },
            'data'=> $blog
        ])->add('locale', HiddenType::class, [
            'data' => $locale
        ]);
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($locale, $blog) {
                $data = $event->getData();
                $locale = $data['locale'] !== null ? $data['locale']: $locale;

                $blog = null;
                $blogs = $this->blogRepository->joinTranslations($locale)->getInstance()->getQuery()->getResult();
                if($blog === null)
                {
                    $blog = reset($blogs);
                }
                $this->blogRepository->clearInstance();
                $event->setData(
                    [
                        'locale' => $locale,
                        'blog' => $blog
                    ]
                );

            }
        );
    }
    private function handleMultipleLocales(FormBuilderInterface $builder, $options)
    {
        $locale = $options['locale'] === null ? reset($this->availableLocales) : $options['locale'];
        $blog = $options['blog'];
        $builder->add('locale', ChoiceType::class, [
            'label'       => 'victoire.blog.choose.locale.label',
            'choices'     => array_combine($this->availableLocales, $this->availableLocales),
            'preferred_choices' => $locale,
            'data' => $locale
        ]);
        $formModifier = function (FormInterface $form, $currentLocale = null, Blog $blog = null) {
            $blogs = $this->blogRepository->joinTranslations($currentLocale)->getInstance()->getQuery()->getResult();
            if($blog === null)
            {
                $blog = reset($blogs);
            }
            $this->blogRepository->clearInstance();
            $form->add('blog', EntityHiddenType::class, [
                'data'=> $blog,
                'class' => Blog::class,
            ]);

        };
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier, $locale, $blog) {
                $data = $event->getData();
                $locale = $data['locale'] !== null ? $data['locale']: $locale;
                $formModifier($event->getForm(), $locale, $blog);

                $blog = null;
                $blogs = $this->blogRepository->joinTranslations($locale)->getInstance()->getQuery()->getResult();
                if($blog === null)
                {
                    $blog = reset($blogs);
                }
                $this->blogRepository->clearInstance();
                $event->setData(
                    [
                        'locale' => $locale,
                        'blog' => $blog
                    ]
                );

            }
        );

        $builder->get('locale')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier, $blog) {
                $locale = $event->getData();
                $formModifier($event->getForm()->getParent(), $locale, $blog);
            }
        );
    }

    /**
     * bind to Page entity.
     *
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => null,
                'translation_domain'   => 'victoire',
                'blog'                 => null,
                'locale'               => null,
            ]
        );
    }
}
