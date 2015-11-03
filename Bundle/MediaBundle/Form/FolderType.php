<?php

namespace Victoire\Bundle\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
     * @param Folder $folder The folder
     */
    public function __construct(Folder $folder = null)
    {
        $this->folder = $folder;
    }

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
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $folder = $this->folder;
        $type = $this;
        $builder
            ->add('name')
            ->add('rel', 'choice', [
                'choices'   => ['media' => 'media', 'image' => 'image', 'slideshow' => 'slideshow', 'video' => 'video'],
                ])
            ->add('parent', 'entity', ['class' => 'Victoire\Bundle\MediaBundle\Entity\Folder', 'required' => false,
                'query_builder'                => function (\Doctrine\ORM\EntityRepository $er) use ($folder, $type) {
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
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'victoire_mediabundle_FolderType';
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
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
                'data_class' => 'Victoire\Bundle\MediaBundle\Entity\Folder',
        ]);
    }
}
