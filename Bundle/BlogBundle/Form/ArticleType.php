<?php
namespace Victoire\Bundle\BlogBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Victoire\Bundle\CoreBundle\DataTransformer\ViewToIdTransformer;

/**
 *
 */
class ArticleType extends AbstractType
{
    private $entityManager;

    /**
     * Constructor
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * define form fields
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $viewToIdTransformer = new ViewToIdTransformer($this->entityManager);
        $blog = $builder->getData()->getBlog();
        $categoryRepo = $this->entityManager->getRepository('Victoire\Bundle\BlogBundle\Entity\Category');
        if ($blog) {
            $queryBuilder = $categoryRepo->getOrderedCategories($blog)->getInstance();
        } else {
            $queryBuilder = $categoryRepo->getAll()->getInstance();
        }
        $builder
            ->add('name')
            ->add('description', null, array(
                'required' => false))
            ->add('image', 'media')
            ->add('category', 'hierarchy_tree', array(
                'class' => "Victoire\Bundle\BlogBundle\Entity\Category",
                'query_builder' => $queryBuilder,
                'empty_value' => "Pas de catégorie",
                "empty_data" => null
                )
            )
            ->add(
                $builder->create('blog', 'hidden', array(
                    'label' => 'form.article.blog.label')
                )->addModelTransformer($viewToIdTransformer)
            )
            ->add(
                'tags',
                'tags',
                array(
                    'required' => false,
                    'multiple' => true
                )
            );

            $articlePatterns = function (EntityRepository $repo) {
                return $repo->getInstance()->andWhere("pattern.businessEntityName = 'article'");
            };
            $builder->add('pattern', null, array(
                'label'         => 'form.view.type.pattern.label',
                'property'      => 'name',
                'required'      => true,
                'query_builder' => $articlePatterns,
            )
        );
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
