<?php
namespace Victoire\Bundle\BlogBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\DataTransformer\ViewToIdTransformer;
use Victoire\Bundle\CoreBundle\Form\ViewType;
use Victoire\Bundle\TemplateBundle\Entity\Template;

/**
 *
 */
class ArticleType extends ViewType
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $viewToIdTransformer = new ViewToIdTransformer($this->em);
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

            $getAllArticleTemplates = function (EntityRepository $tr) {
                return $tr->getPatterns()
                    ->getInstance()
                    ->andWhere("pattern.businessEntityName = 'article'");
            };
            $builder->add('template', null, array(
                'label'         => 'form.view.type.template.label',
                'property'      => 'name',
                'required'      => true,
                'query_builder' => $getAllArticleTemplates,
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
