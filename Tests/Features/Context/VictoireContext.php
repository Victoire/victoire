<?php

namespace Victoire\Tests\Features\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

use Behat\Symfony2Extension\Context\KernelDictionary;
use Knp\FriendlyContexts\Context\MinkContext;
use Knp\FriendlyContexts\Context\RawMinkContext;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * This class gives some usefull methods for Victoire navigation
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
     * @param BeforeScenarioScope $scope
     */
    public function resetViewsReference(BeforeScenarioScope $scope)
    {
        $views = $this->getContainer()->get('doctrine.orm.entity_manager')->createQuery("SELECT v FROM VictoireCoreBundle:View v")->getResult();
        $viewsReferences = $this->getContainer()->get('victoire_core.view_helper')->buildViewsReferences($views);
        $this->getContainer()->get('victoire_core.view_cache_helper')->write($viewsReferences);
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
        $element = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::*[@data-mode="admin-'.$mode.'"]');
        $element->click();
    }

    /**
     * @Then /^I open the hamburger menu$/
     */
    public function iOpenTheHamburgerMenu()
    {
        $element = $this->getSession()->getPage()->find('xpath', 'descendant-or-self::*[@id="vic-menu-leftnavbar-trigger"]');
        $element->click();
    }

    /**
     * @When I follow the tab :name
     */
    public function iFollowTheTab($name)
    {
        $tab = $this->getSession()->getPage()->find('xpath', sprintf('descendant-or-self::a[@data-toggle="vic-tab" and normalize-space(text()) = "%s"]', $name));
        $tab->click();
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
        $element = $this->getSession()->getPage()->find(
            'xpath',
            sprintf('//descendant-or-self::*[normalize-space(text()) = "%s"]/ancestor::div[@class="vic-widget-container"]/following-sibling::div[@class="vic-widget-container"]/descendant-or-self::*[normalize-space(text()) = "%s"]', $textBefore, $textAfter)
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
}
