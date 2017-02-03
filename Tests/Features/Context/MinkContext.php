<?php

namespace Victoire\Tests\Features\Context;

use Behat\Mink\Element\Element;
use Behat\Mink\Exception\ExpectationException;
use Knp\FriendlyContexts\Context\MinkContext as KFMinkContext;

/**
 * Feature context.
 */
class MinkContext extends KFMinkContext
{
    /**
     * Checks, that page contains specified text
     * Example: Then I should see "Who is the Batman?"
     * Example: And I should see "Who is the Batman?"
     */
    public function assertPageContainsText($text, $timeout = 10000)
    {
        $element = $this->findOrRetry($this->getSession()->getPage(), $text, $timeout);
        if (!$element) {
            $message = sprintf('The text "%s" was not found anywhere in the text of the current page.', $text);
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Checks, that page doesn't contain specified text
     * Example: Then I should not see "Batman is Bruce Wayne"
     * Example: And I should not see "Batman is Bruce Wayne"
     */
    public function assertPageNotContainsText($text, $timeout = 10000)
    {
        $element = $this->findOrRetry($this->getSession()->getPage(), $text, $timeout);
        if ($element && $element->isVisible()) {
            $message = sprintf('The text "%s" was found in the text of the current page although it should not.', $text);
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Checks, that element with specified CSS contains specified text
     * Example: Then I should see "Batman" in the "heroes_list" element
     * Example: And I should see "Batman" in the "heroes_list" element
     *
     */
    public function assertElementContainsText($element, $text, $timeout = 10000)
    {
        if ($timeout <= 0) {
            $message = sprintf('The element "%s" was not found in the page.', $element);
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
        $selectorType = 'css';

        $node = $this->getSession()->getPage()->find($selectorType, $element);

        if (is_object($node)) {
            $item = $this->findOrRetry($node, $text);
            if (!$item) {
                $this->assertElementContainsText($element, $text, 0);
            }
        } else {
            $this->getSession()->wait(100);

            return $this->assertElementContainsText($element, $text, $timeout - 100);
        }
    }

    /**
     * @param Element   $page
     * @param float|int $timeout
     */
    public function assertPageAddress($page, $timeout = 10000)
    {
        try {
            $this->assertSession()->addressEquals($this->locatePath($page));
        } catch (ExpectationException $e) {
            if ($timeout >= 0) {
                $this->getSession()->wait(100);

                return $this->assertPageAddress($page, $timeout - 100);
            }
        }
    }

    /**
     * Try to find value in element and retry for a given time
     * @param Element $element
     * @param string  $value
     * @param integer $timeout
     */
    protected function findOrRetry(Element $element, $value, $timeout = 10000)
    {
        if ($timeout <= 0) {
            return false;
        }

        // Hack to do an insensitive case search
        $alphabetLower = '"'.implode('', range('a', 'z')).'"';
        $alphabetUpper = '"'.implode('', range('A', 'Z')).'"';

        $item = $element->find('xpath', '/descendant-or-self::*[contains(translate(text(), '.$alphabetUpper.', '.$alphabetLower.'), translate("'.$value.'", '.$alphabetUpper.', '.$alphabetLower.'))]');

        if ($item) {
            return $item;
        } else {
            $this->getSession()->wait(100);

            return $this->findOrRetry($element, $value, $timeout - 100);
        }

    }
}
