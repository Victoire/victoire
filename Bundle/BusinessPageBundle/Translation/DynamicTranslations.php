<?php

namespace Victoire\Bundle\BusinessPageBundle\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 *
 */
class DynamicTranslations implements TranslationContainerInterface
{
    /**
     * usage example: new Message('example.keymap')->addSource('path/to/source/file', '514', '10'),.
     *
     * @return array the keys to register in jms translation
     */
    public static function getTranslationMessages()
    {
        return [
            new Message('form.page.type.layout.label'),
        ];
    }
}
