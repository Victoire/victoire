<?php

namespace Victoire\Tests\Features\Context\SubContext;

/**
 * This trait give some usefull methods for ajax navigation
 */
trait AjaxSubContextTrait
{
    /**
     * Checks, that page contains specified text.
     * @param string  $text    the text to check
     * @param integer $timeout in milliseconds
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageContainsText($text, $timeout = 5000)
    {
        $element = $this->findAfterAjax($this->getSession()->getPage(), $text, $timeout);
        if (!$element) {
            $message = sprintf('The text "%s" was not found anywhere in the text of the current page.', $text);
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
    }

    /**
    * Checks, that page does not contain specified text.
     * @param string  $text    the text to check
     * @param integer $timeout in milliseconds
     *
     * @Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)"$/
    */
    public function assertPageNotContainsText($text, $timeout = 5000)
    {
        $element = $this->findAfterAjax($this->getSession()->getPage(), $text, $timeout);
        if ($element && $element->isVisible()) {
                $message = sprintf('The text "%s" was found in the text of the current page although it should not.', $text);
                throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
    }

    /**
    * Checks, that element with specified CSS contains specified text.
     * @param string  $element the element where search
     * @param string  $text    the text to check
     * @param integer $timeout in milliseconds
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in the "(?P<element>[^"]*)" element$/
     *
     * @return boolean
    */
    public function assertElementContainsText($element, $text, $timeout = 5000)
    {
       if ($timeout <= 0) {
           $message = sprintf('The element "%s" was not found in the page.', $element);
           throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
       }
       $selectorType = 'css';

       $node = $this->getSession()->getPage()->find($selectorType, $element);

       if (is_object($node)) {
           $item = $this->findAfterAjax($node, $text);
           if (!$item) {
               $this->assertElementContainsText($element, $text, 0);
           }
       } else {
           $this->getSession()->wait(100);

           return $this->assertElementContainsText($element, $text, $timeout-100);
       }
    }

   /**
    * Try to select element, return null after 15 sec
     * @param string  $element the element where search
     * @param string  $value   the value to check
     * @param integer $timeout in milliseconds
     *
     * @return boolean
    */
   public function findAfterAjax($element, $value, $timeout = 5000)
   {
       if ($timeout <= 0) {
           return false;
       }

       // Hack to be able to get an element case insensitively... How amazing is this code ? hu ?
       $alphabetLower = '"'.implode('', range('a', 'z')).'"';
       $alphabetUpper = '"'.implode('', range('A', 'Z')).'"';

       $item = $element->find('xpath', '/descendant-or-self::*[contains(translate(text(), '.$alphabetUpper.', '.$alphabetLower.'), translate("' . $value. '", '.$alphabetUpper.', '.$alphabetLower.'))]');

       if ($item) {
           return $item;
       } else {
           $this->getSession()->wait(100);

           return $this->findAfterAjax($element, $value, $timeout-100);
       }

   }

}
