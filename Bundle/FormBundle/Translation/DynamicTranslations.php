<?php

namespace Victoire\Bundle\FormBundle\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * Add dynamic translation for form
 */
class DynamicTranslations implements TranslationContainerInterface
{
    /**
     * usage example: new Message('example.keymap')->addSource('path/to/source/file', '514', '10'),
     * @return array the keys to register in jms translation
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message('validator.link.error.message.pageMissing', 'victoire'),
            new Message('validator.link.error.message.routeMissing', 'victoire'),
            new Message('validator.link.error.message.urlMissing', 'victoire'),
            new Message('validator.link.error.message.attachedWidgetMissing', 'victoire'),
        );
    }

}
