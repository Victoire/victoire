<?php

namespace Victoire\Bundle\BusinessEntityBundle\Handler;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Victoire\Bundle\BusinessEntityBundle\Transliterator\Transliterator;
use Victoire\Bundle\BusinessPageBundle\Entity\BusinessTemplate;

/**
 * Sluggable handler which keep twig variable after urlization.
 */
class TwigSlugHandler implements SlugHandlerInterface
{
    /**
     * @var SluggableListener
     */
    protected $sluggable;

    /**
     * {@inheritdoc}
     */
    public function __construct(SluggableListener $sluggable)
    {
        $this->sluggable = $sluggable;
    }

    /**
     * {@inheritdoc}
     */
    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
        $this->sluggable->setTransliterator([$this, 'transliterate']);
        $needToChangeSlug = true;
    }

    /**
     * {@inheritdoc}
     */
    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function validate(array $options, ClassMetadata $meta)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        $this->sluggable->setTransliterator([$this, 'transliterate']);
    }

    /**
     * Transliterates the slug and keep twig variable.
     *
     * @param string $text
     * @param string $separator
     * @param object $object
     *
     * @return string
     */
    public function transliterate($text, $separator, $object)
    {
        if ($object instanceof BusinessTemplate
            || (in_array(Translatable::class, class_uses($object))
            && $object->getTranslatable() instanceof BusinessTemplate)) {
            $slug = Transliterator::urlize($text, $separator, true);
        } else {
            $slug = Transliterator::urlize($text, $separator);
        }

        return $slug;
    }

    /**
     * {@inheritdoc}
     */
    public function handlesUrlization()
    {
        return true;
    }
}
