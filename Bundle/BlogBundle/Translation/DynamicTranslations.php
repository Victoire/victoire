<?php
namespace Victoire\Bundle\BlogBundle\Translation;


use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 *
 * @author Thomas Beaujean
 *
 */
class DynamicTranslations implements TranslationContainerInterface
{
    /**
     * Get the translations
     *
     * @return multitype:\JMS\TranslationBundle\Model\Message
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message('menu.blog', array(), 'victoire'),
            new Message('menu.blog.settings', array(), 'victoire'),
            new Message('menu.blog.new', array(), 'victoire'),
            new Message('widget_filter.category_filter', array(), 'victoire'),
            new Message('widget_filter.tag_filter', array(), 'victoire')
        );
    }
}
