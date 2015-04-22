<?php

namespace Victoire\Tests\Features\Context\SubContext;

use Behat\Behat\Context\Step;

/**
 * This trait give some usefull methods for ajax navigation
 */
trait VictoireSubContextTrait
{
    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        return $this->iAmLoggedInAsWithPassword($username, 'test');
    }

    /**
     * @Given /^I am logged in as "([^"]*)" with password "([^"]*)"$/
     * @param string $password
     */
    public function iAmLoggedInAsWithPassword($username, $password)
    {
        return array(
            new Step\Given('I am on "/login"'),
            new Step\When('I fill in "username" with "'.$username.'"'),
            new Step\When('I fill in "password" with "'.$password.'"'),
            new Step\When('I press "_submit"'),
        );
    }
    /**
     * @Then /^I fill in wysiwyg with "([^"]*)"$/
     */
    public function iFillInWysiwygOnFieldWith($arg)
    {
        $js = 'CKEDITOR.instances.victoire_widget_form_ckeditor_content.setData("'.$arg.'");';
        $this->getSession()->executeScript($js);
    }

    /**
     * @Then /^I select "([^"]*)" from the "([^"]*)" select of "([^"]*)" slot$/
     */
    public function iSelectFromTheSelectOfSlot($widget, $nth, $slot)
    {
        $widget = $this->fixStepArgument($widget);
        $element = 'descendant-or-self::*[@id="vic-widget-1-container"]/div['.$nth.']/label/select';
        $element = 'descendant-or-self::*[@id="vic-slot-'.$slot.'"]/div/label/select['.$nth.']';

        $slot = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::*[@id="vic-slot-'.$slot.'"]');
        $selects = $slot->findAll('css', 'select[role="menu"]');
        $selects[$nth - 1]->selectOption($widget);
    }

    /**
     * @Then /^I switch to "([^"]*)" mode$/
     */
    public function iSwitchToMode($mode)
    {
        $element = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::*[@data-mode="admin-'.$mode.'"]');
        $element->click();
    }
    /**
     * @Then /^I submit the widget$/
     */
    public function iSubmitTheWidget()
    {
        $element = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::*[@class="vic-modal-footer-content"]/a[@data-modal="create"]');
        if (!$element) {
            $element = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::*[@class="vic-modal-footer-content"]/a[@data-modal="update"]');
        }
        $element->click();
    }

    /**
     * @Given /^I edit an "([^"]*)" widget$/
     * @Given /^I edit the "([^"]*)" widget$/
     */
    public function iEditTheWidget($widgetType)
    {
        $selector = sprintf('.vic-widget-%s > a', strtolower($widgetType));
        $session = $this->getSession(); // get the mink session
        $element = $session->getPage()->find('css', $selector); // runs the actual query and returns the element

        // errors must not pass silently
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $selector));
        }

        // ok, let's hover it
        $element->mouseOver();
        $element->click();
    }

    /**
     * @Then /^"([^"]*)" should precede "([^"]*)"$/
     */
    public function shouldPrecedeForTheQuery($textBefore, $textAfter)
    {
        $items = array_map(
            function($element) {
                return $element->getText();
            },
            $this->getSession()->getPage()->findAll('css', 'div.vic-widget > p')
        );

        if (array_search($textBefore, $items) > array_search($textAfter, $items)) {
            $message = "$textBefore does not proceed $textAfter";
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * @When /^I select the option "(?P<option>[^"]*)" in the dropdown "(?P<dropdown>[^"]*)"$/
     */
    public function iSelectTheOptionInTheDropdown($option, $dropdown)
    {
        $link = $this->getSession()->getPage()->find('css', sprintf('a.vic-dropdown-toggle[title="%s"]', $dropdown));
        $link->click();
        $optionButton = $this->getSession()->getPage()->find('css', sprintf('ul[aria-labelledby="%sDropdownMenu"] > li > a[title="%s"]', $dropdown, $option));
        $optionButton->click();
    }
}
