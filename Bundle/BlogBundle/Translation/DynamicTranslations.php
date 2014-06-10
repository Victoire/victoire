<?php
namespace Victoire\Bundle\BlogBundle\Translation;


use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

class DynamicTranslations implements TranslationContainerInterface
{
    public static function getTranslationMessages()
    {
        return array(
            new Message('menu.blog', array(), 'victoire'),
            new Message('menu.blog.settings', array(), 'victoire'),
            new Message('menu.blog.new', array(), 'victoire'),
            new Message('widget_filter.category_filter', array(), 'victoire'),
            new Message('widget_filter.tag_filter', array(), 'victoire'),
        );
    }
}
