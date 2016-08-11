<?php

namespace Victoire\Tests\Features\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Element\Element;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Knp\FriendlyContexts\Context\MinkContext;
use Knp\FriendlyContexts\Context\RawMinkContext;

/**
 * This class gives some usefull methods for Victoire navigation.
 *
 * @property MinkContext minkContext
 */
class VictoireContext extends RawMinkContext
{
    use KernelDictionary;
    protected $minkContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->minkContext = $environment->getContext('Knp\FriendlyContexts\Context\MinkContext');
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function resetViewsReference(BeforeScenarioScope $scope)
    {
        $viewsReferences = $this->getContainer()->get('victoire_core.view_helper')->buildViewsReferences();
        $this->getContainer()->get('victoire_view_reference.manager')->saveReferences($viewsReferences);
    }

    /**
     * @Given I am logged in as :email
     */
    public function iAmLoggedInAsUser($email)
    {
        $this->minkContext->visit('/login');
        $this->minkContext->fillField('username', $email);
        $this->minkContext->fillField('password', 'test');
        $this->minkContext->pressButton('_submit');
    }

    /**
     * @Given I login as visitor
     */
    public function iLoginAsVisitor()
    {
        $this->getSession()->getDriver()->stop();
        $url = 'http://z6po@victoire.io:test@fr.victoire.io:8000';
        $this->minkContext->setMinkParameter('base_url', $url);
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
        $slot = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::*[@id="vic-slot-'.$slot.'"]');
        $selects = $slot->findAll('css', 'select[role="menu"]');
        $selects[$nth - 1]->selectOption(str_replace('\\"', '"', $widget));
    }

    /**
     * @Then /^I switch to "([^"]*)" mode$/
     */
    public function iSwitchToMode($mode)
    {
        $element = $this->findOrRetry($this->getSession()->getPage(), 'xpath', 'descendant-or-self::*[@data-mode="admin-'.$mode.'"]');

        if (null === $element) {
            $message = sprintf('Element not found in the page after 10 seconds"');
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
        $element->click();
    }

    /**
     * @Then /^I (open|close|toggle) the hamburger menu$/
     */
    public function iOpenTheHamburgerMenu()
    {
        $element = $this->findOrRetry($this->getSession()->getPage(), 'xpath', 'descendant-or-self::*[@id="vic-menu-leftnavbar-trigger"]');

        if (null === $element) {
            $message = sprintf('Element not found in the page after 10 seconds"');
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
        $element->click();
    }

    /**
     * @When I follow the tab :name
     */
    public function iFollowTheTab($name)
    {
        $element = $this->findOrRetry($this->getSession()->getPage(), 'xpath', sprintf('descendant-or-self::a[@data-toggle="vic-tab" and normalize-space(text()) = "%s"]', $name));

        if (null === $element) {
            $message = sprintf('Element not found in the page after 10 seconds"');
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
        $element->click();
    }

    /**
     * @Then /^I submit the widget$/
     * @Then /^I submit the modal$/
     */
    public function iSubmitTheWidget()
    {
        $element = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::a[@data-modal="create"]');

        if (!$element) {
            $element = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::a[@data-modal="update"]');
        }
        $element->click();
    }

    /**
     * @Given /^I edit an "([^"]*)" widget$/
     * @Given /^I edit the "([^"]*)" widget$/
     */
    public function iEditTheWidget($widgetType)
    {
        $selector = sprintf('.vic-widget-%s > a.vic-hover-widget', strtolower($widgetType));
        $session = $this->getSession(); // get the mink session
        $element = $this->findOrRetry($session->getPage(), 'css', $selector);

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
        $element = $this->getSession()->getPage()->find(
            'xpath',
            sprintf('//descendant-or-self::*[normalize-space(text()) = "%s"]/ancestor::div/descendant-or-self::*[normalize-space(text()) = "%s"]', $textBefore, $textAfter)
        );

        if (null === $element) {
            $message = sprintf('"%s" does not preceed "%s"', $textBefore, $textAfter);
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

    /**
     * @Then /^I attach image with id "(\d+)" to victoire field "(.+)"$/
     */
    public function attachImageToVictoireScript($imageId, $fieldId)
    {
        $script = sprintf('$("#%s input").val(%d)', $fieldId, $imageId);
        $this->getSession()->executeScript($script);
    }

    /**
     * @Then I should find css element :element with selector :selector and value :value
     */
    public function iShouldFindCssWithSelectorAndValue($element, $selector, $value)
    {
        $css = sprintf('%s[%s="%s"]', $element, $selector, $value);
        $session = $this->getSession();
        $element = $this->findOrRetry($session->getPage(), 'css', $css);

        if (null === $element) {
            $message = sprintf('Element not found. String generate: %s[%s="%s"]', $element, $selector, $value);
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Try to find value in element and retry for a given time.
     *
     * @param Element $element
     * @param string  $selectorType xpath|css
     * @param string  $value
     * @param int     $timeout
     */
    protected function findOrRetry(Element $element, $selectorType, $value, $timeout = 10000)
    {
        if ($timeout <= 0) {
            return;
        }

        $item = $element->find($selectorType, $value);

        if ($item) {
            return $item;
        } else {
            $this->getSession()->wait(100);

            return $this->findOrRetry($element, $selectorType, $value, $timeout - 100);
        }
    }

    /**
     * @Then I should see disable tab :name
     */
    public function iShouldSeeDisableTab($name)
    {
        $element = $this->findOrRetry($this->getSession()->getPage(), 'xpath', sprintf('descendant-or-self::li[@class="vic-disable" and normalize-space(.) = "%s"]', $name));

        if (null === $element) {
            $message = sprintf('Element not found in the page after 10 seconds"');
            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * @Then /^I move the widgetMap "(.+)" "(.+)" the widgetMap "(.*)"$/
     */
    public function iMoveWidgetUnder($widgetMapMoved, $position, $widgetMapMovedTo)
    {
        if (!$widgetMapMovedTo) {
            $widgetMapMovedTo = 'null';
        }
        $js = 'updateWidgetPosition({"parentWidgetMap": '.$widgetMapMovedTo.', "slot": "main_content", "position": "'.$position.'", "widgetMap": '.$widgetMapMoved.'})';

        $this->getSession()->executeScript($js);
    }
}
