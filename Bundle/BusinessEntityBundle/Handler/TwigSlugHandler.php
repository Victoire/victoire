<?php

namespace Victoire\Bundle\BusinessEntityBundle\Handler;

use Gedmo\Sluggable\SluggableListener;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Victoire\Bundle\BusinessEntityBundle\Transliterator\Transliterator;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Victoire\Bundle\BusinessEntityPageBundle\Entity\BusinessTemplate;

/**
 * Sluggable handler which keep twig variable after urlization
 *
 */
class TwigSlugHandler implements SlugHandlerInterface
{
    /**
     * @var SluggableListener
     */
    protected $sluggable;


    /**
     * {@inheritDoc}
     */
    public function __construct(SluggableListener $sluggable)
    {
        $this->sluggable = $sluggable;
        $this->transliterator = new Transliterator();
    }

    /**
     * {@inheritDoc}
     */
    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
        $this->sluggable->setTransliterator(array($this, 'transliterate'));
        $needToChangeSlug = true;
    }

    /**
     * {@inheritDoc}
     */
    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function validate(array $options, ClassMetadata $meta)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        $this->sluggable->setTransliterator(array($this, 'transliterate'));
    }

    /**
     * Transliterates the slug and keep twig variable
     *
     * @param string $text
     * @param string $separator
     * @param object $object
     *
     * @return string
     */
    public function transliterate($text, $separator, $object)
    {
        if ($object instanceof BusinessTemplate) {
            $slug = $this->transliterator->urlize($text, $separator, true);
        } else {
            $slug = $this->transliterator->urlize($text, $separator);
        }
        return $slug;
    }

    /**
     * {@inheritDoc}
     */
    public function handlesUrlization()
    {
        return true;
    }
}
