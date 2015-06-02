<?php

namespace Victoire\Bundle\BusinessEntityBundle\Transliterator;

use Behat\Transliterator\Transliterator as BaseTransliterator;

/**
* Transliterator
*/
class Transliterator extends BaseTransliterator
{

    /**
     * Does not transliterate correctly eastern languages
     *
     * @param string $text
     * @param string $separator
     *
     * @return string
     */
    public static function urlize($text, $separator = '-', $excludeTwig = false)
    {
        $text = parent::unaccent($text);

        return self::postProcessText($text, $separator, $excludeTwig);
    }
    /**
     * Cleans up the text and adds separator and keep twig variable
     *
     * @param string $text
     * @param string $separator
     *
     * @return string
     */
    private static function postProcessText($text, $separator, $excludeTwig)
    {

        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text);
        } else {
            $text = strtolower($text);
        }
        if (!$excludeTwig)
        {;
            $text = preg_replace('/\W/', ' ', $text);

        }
        $text = preg_replace('/::/', '/', $text);
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\1_\2', $text);

        if ($excludeTwig)
        {
            // this regex match all word, characters, number, twig variables.
            $text = preg_replace("/(?:\\{\\{\\s*%\\s*[^}]+\\}\\})|(\\{\\{\\s*(?!%)\\s*(?>(?!\\.)[^\\s{}%]*)(?<!%)\\s*\\}\\}|\\w+)|(?:.)/mx", "$1 ", $text);
            $text = preg_replace('!\s+!', $separator, $text);
        }else{
            $text = preg_replace('/[^A-Za-z0-9\/]+/', $separator, $text);

        }
        $text = strtolower($text);
        $text = trim($text, '-');
        return $text;
    }
}