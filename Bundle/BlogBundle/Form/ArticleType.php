<?php
namespace Victoire\Bundle\BlogBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\DataTransformer\ViewToIdTransformer;
use Victoire\Bundle\PageBundle\Form\BasePageType;
use Victoire\Bundle\TemplateBundle\Entity\Template;

/**
 *
 */
class ArticleType extends BasePageType
{
    private $entityManager;

    /**
    * Constructor
    */
    public function __construct(EntityManager $entityManager, $availableLocales, RequestStack $requestStack)
    {
        parent::__construct($availableLocales, $requestStack);
        $this->entityManager = $entityManager;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $viewToIdTransformer = new ViewToIdTransformer($this->entityManager);
        $builder
            ->add('description')
            ->add('image', 'media')
            ->add('category')
            ->add(
                $builder->create('blog', 'hidden', array(
                    'label' => 'form.article.blog.label')
                )->addModelTransformer($viewToIdTransformer)
            )
            ->add(
                'tags',
                'select2',
                array(
                    'class'    => 'Victoire\Bundle\BlogBundle\Entity\Tag',
                    'property' => 'title',
                    'required' => false,
                    'multiple' => true
                )
            );

            $articleTemplates = function (EntityRepository $repo) {
                return $repo->getPatterns()
                    ->getInstance()
                    ->andWhere("pattern.businessEntityName = 'article'");
            };
            $builder->add('template', null, array(
                'label'         => 'form.view.type.template.label',
                'property'      => 'name',
                'required'      => true,
                'query_builder' => $articleTemplates,
            ));

    }

    /**
     * bind to Page entity
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Victoire\Bundle\BlogBundle\Entity\Article',
            'translation_domain' => 'victoire'
        ));
    }

    /**
     * get form name
     *
     * @return string The name of the form
     */
    public function getName()
    {
        return 'victoire_article_type';
    }
}
