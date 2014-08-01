<?php
namespace Victoire\Bundle\CoreBundle\Features\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\ResponseTextException;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    const MAX_WAIT = 10000;

    private $params = array();
    private $defaultDriver;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters Context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {

        $this->useContext('page', new PageContext($parameters));
        $this->useContext('entity', new EntityContext($parameters));
        $this->params = $parameters;
    }

    /**
     * @When /^I wait ([0-9]*) seconds$/
     */
    public function iWaitSeconds($seconds)
    {
        sleep($seconds);
    }

    /**
     * @Then /^I select the option "(?P<option>[^"]*)" in the dropdown "(?P<dropdown>[^"]*)"$/
     */
    public function iSelectTheOptionInTheDropdown($option, $dropdown)
    {
        $link           = $this->getSession()->getPage()->findLink($dropdown);
        if (!$link) {
                $message = sprintf('The dropdown %s was not found in page', $dropdown);
                throw new ResponseTextException($message, $this->getSession());
        }
        $dropdownButton = $link->getParent()->find('css', '.dropdown-toggle');
        $optionButton   = $link->getParent()->findLink($option);

        $dropdownButton->click();
        $optionButton->click();
    }

    /**
     * @Then /^I should see the option "(?P<option>[^"]*)" in the dropdown "(?P<dropdown>[^"]*)"$/
     */
    public function iShouldSeeTheOptionInTheDropdown($option, $dropdown)
    {
        $link = $this->getSession()->getPage()->findLink($dropdown);
        assertNotNull($link);
        $dropdownButton = $link->getParent()->find('css', '.dropdown-toggle');
        assertNotNull($dropdownButton);
        $optionButton = $link->getParent()->findLink($option);
        assertNotNull($optionButton);

        $dropdownButton->click();

        assertTrue($optionButton->isVisible());

        $dropdownButton->click();
    }

    /**
     * try to select element, return null after 15 sec
     *
     * @param
     *
     * @return null|element
     * @author
     **/
    public function findElementAfterAjax($element, $value, $timeout = 15000)
    {
        $alphabetLower = '"'.implode('', range('a', 'z')).'"';
        $alphabetUpper = '"'.implode('', range('A', 'Z')).'"';

        $locator = '/descendant-or-self::*[contains(translate(node(), '.$alphabetUpper.', '.$alphabetLower.'), translate("' . $value. '", '.$alphabetUpper.', '.$alphabetLower.'))]';

        return $this->findAfterAjax($element, 'xpath', $locator, $timeout);

    }

    /**
     * try to select element, return null after 15 sec
     *
     * @param
     *
     * @return null|element
     * @author
     **/
    public function findAfterAjax($element, $selector, $locator, $timeout = 15000)
    {
        if ($timeout <= 0) {
            return false;
        }

        $item = $element->find($selector, $locator);

        if ($item) {
            return $item;
        } else {
            $this->getSession()->wait(100);

            return $this->findAfterAjax($element, $selector, $locator, $timeout-100);
        }

    }

    /**
     * Checks, that page contains specified text.
     *
     */
    public function assertPageContainsText($text)
    {
        $element = $this->findElementAfterAjax($this->getSession()->getPage(), $text);
        if (!$element) {
                $message = sprintf('The text "%s" was not found anywhere in the text of the current page.', $text);
                throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Checks, that page does not contain specified text.
     *
     */
    public function assertPageNotContainsText($text)
    {
        $element = $this->findElementAfterAjax($this->getSession()->getPage(), $text, 1000);
        if ($element && $element->isVisible()) {
                $message = sprintf('F**k - The text "%s" was found in the text of the current page although it should not.', $text);
                throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * @When /^I fill redactor "([^"]*)" field with "([^"]*)"$/
     */
    public function iFillRedactorFieldWithWidgetRedactor($selector, $value)
    {
        $page = $this->getSession()->getPage();
        $redactor = $this->findAfterAjax($page, "css", "div#".$selector." div.redactor_editor");
        $redactor->setValue($value);
    }

        /**
     * @Then /^I should see "([^"]*)" image$/
     */
    public function iShouldSeeImage($path)
    {

        $locator = "img[src='".$path."']";
        $element = $this->findAfterAjax($this->getSession()->getPage(), 'css', $locator, 1000);
        if (!$element) {
                $message = sprintf('The image with src "%s" was not found anywhere in the text of the current page.', $text);
                throw new ResponseTextException($message, $this->getSession());
        }
    }
        /**
     * @Then /^I should not see "([^"]*)" image$/
     */
    public function iShouldNotSeeImage($path)
    {

        $locator = "img[src='".$path."']";
        $element = $this->findAfterAjax($this->getSession()->getPage(), 'css', $locator, 1000);
        if ($element) {
                $message = sprintf('F**k - The image with src "%s" was found in the text of the current page although it should not.', $text);
                throw new ResponseTextException($message, $this->getSession());
        }
    }

        /**
     * @Then /^I drag widget "([^"]*)" after widget "([^"]*)"$/
     */
    public function iDragWidgetAfterWidget($locator1, $locator2)
    {
        $page = $this->getSession()->getPage();
        $widget1 = $this->findAfterAjax($page, 'css', $locator1);
        $widget1handle = $this->findAfterAjax($page, 'css', $locator1 . ' .block-handle');
        $widget2 = $this->findAfterAjax($page, 'css', $locator2);
        $widget1->dragto($widget1handle);
        $widget1handle->dragto($widget2);
        //TODO: cursor mooves too fast, position is not correctly set. maybe manually execute js script
        // to moove cursor slower


    }

    /**
     * @When /^I click on "([^"]*)" "([^"]*)" widget action$/
     */
    public function iClickOnWidgetAction($action, $widget)
    {

        $script = "$('#".$widget." .control-well').show();";
        $this->getSession()->getDriver()->executeScript($script);
        $actionLocator = "a[id='".$action."-".$widget."']";

        $actionElement = $this->findAfterAjax($this->getSession()->getPage(), 'css', $actionLocator, 500);
        if (!$actionElement) {
                $message = sprintf('The widget action "%s" was not found anywhere in the text of the current page.', $action);
                throw new ResponseTextException($message, $this->getSession());
        }
        $actionElement->click();

    }

}
