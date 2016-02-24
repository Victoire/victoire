<?php

namespace Victoire\Bundle\MediaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Victoire\Bundle\MediaBundle\Entity\Folder;

/**
 * FolderType.
 */
class FolderType extends AbstractType
{
    /**
     * @var Folder
     */
    public $folder;

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $folder = $options['folder'];
        $type = $this;
        $builder
            ->add('name')
            ->add('rel', ChoiceType::class, ['choices' => ['media', 'image', 'slideshow', 'video']])
            ->add('parent', EntityType::class, ['class' => 'Victoire\Bundle\MediaBundle\Entity\Folder', 'required' => false,
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($folder, $type) {
                    $qb = $er->createQueryBuilder('folder');

                    if ($folder != null && $folder->getId() != null) {
                        $ids = 'folder.id != '.$folder->getId();
                        $ids .= $type->addChildren($folder);
                        $qb->andwhere($ids);
                    }
                    $qb->andWhere('folder.deleted != true');

                    return $qb;
                },
        ]);
    }

    /**
     * @param Folder $folder
     *
     * @return string
     */
    public function addChildren(Folder $folder)
    {
        $ids = '';
        foreach ($folder->getChildren() as $child) {
            $ids .= ' and folder.id != '.$child->getId();
            $ids .= $this->addChildren($child);
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Victoire\Bundle\MediaBundle\Entity\Folder',
        ]);
        $resolver->setDefined([
            'folder',
        ]);
    }
}
