<?php

namespace Victoire\Tests\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Driver\Goutte\Client;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\Element;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Session;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Driver\KernelDriver;
use Knp\FriendlyContexts\Context\RawMinkContext;
use Twig\Node\Node;

/**
 * Feature context.
 */
class FeatureContext extends RawMinkContext implements Context, SnippetAcceptingContext, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^I wait (\d+) second$/
     * @Given /^I wait (\d+) seconds$/
     *
     * @param $nbr
     */
    public function iWaitSeconds($nbr)
    {
        /** @var Session $session */
        $session = $this->getSession();
        $session->wait($nbr * 1000);
    }

    public function getSymfonyProfile()
    {
        /** @var Session $session */
        $session = $this->getSession();
        /** @var DriverInterface $driver */
        $driver = $session->getDriver();
        if (!$driver instanceof KernelDriver) {
            throw new UnsupportedDriverActionException(
                'You need to tag the scenario with '.
                '"@mink:symfony2". Using the profiler is not '.
                'supported by %s', $driver
            );
        }

        $profile = $driver->getClient()->getProfile();
        if (false === $profile) {
            throw new \RuntimeException(
                'The profiler is disabled. Activate it by setting '.
                'framework.profiler.only_exceptions to false in '.
                'your config'
            );
        }

        return $profile;
    }

    /**
     * @Then /^I should see the css property "(.+)" of "(.+)" with "(.+)"$/
     *
     * @param string $property
     * @param $elementId
     * @param string $value
     */
    public function iShouldSeeCssOfWith($property, $elementId, $value)
    {
        /** @var Session $session */
        $session = $this->getSession();
        $script = "return $('#".$elementId."').css('".$property."') === '".$value."';";
        $evaluated = $session->evaluateScript($script);
        if (!$evaluated) {
            throw new \RuntimeException('The element with id "'.$elementId.'" and css property "'.$property.': '.$value.';" not found.');
        }
    }

    /**
     * @Then I should see background-image of :id with relative url :url
     *
     * @param $id
     * @param $url
     */
    public function iShouldSeeBackgroundImageWithRelativeUrl($id, $url)
    {
        /** @var Session $session */
        $session = $this->getSession();
        $base_url = $session->getCurrentUrl();
        $parse_url = parse_url($base_url);
        $base_url = rtrim($base_url, $parse_url['path']);
        $url = rtrim($base_url, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($url, DIRECTORY_SEPARATOR);
        $this->iShouldSeeCssOfWith('background-image', $id, 'url("'.$url.'")');
    }

    /**
     * @Then the title should be :title
     *
     * @param $title
     *
     * @throws ResponseTextException
     */
    public function theTitleShouldBe($title)
    {
        $element = $this->getPage()->find(
            'xpath',
            sprintf('//title[normalize-space(text()) = "%s"]', $title)
        );

        if (null === $element) {
            $message = sprintf('"%s" is not the title of the page', $title);

            throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * @Then The modal title should be :title
     *
     * @param $title
     *
     * @throws ResponseTextException
     */
    public function theModalTitleShouldBe($title)
    {
        $element = $this->getPage()->find('xpath', '//*[@id="victoire-modal-label"]');

        if ($title !== $element->getText()) {
            $message = sprintf('"%s" is not the modal title, the title is "%s"', $title, $element->getText());

            throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * @Then I follow :arg1 from the :arg2 dropdown menu
     *
     * @param string $dropdownItem Level 1 menu item title
     * @param string $mainItem     Level 0 menu item title
     *
     * @throws ResponseTextException
     */
    public function iFollowFromTheMenu($dropdownItem, $mainItem)
    {
        /** @var Session $session */
        $session = $this->getSession();

        $mainElement = $this->getPage()->find('xpath', '//li[@id="'.$mainItem.'"]');
        $session->wait(2000);

        if (null === $mainElement) {
            throw new ResponseTextException(sprintf('"%s" element id was not found in the menu', $mainItem), $this->getSession());
        }

        $mainElement->click();
        $subElement = $mainElement->find('xpath', '//li[@id="'.$dropdownItem.'"]');
        $session->wait(2000);

        if (null === $subElement) {
            throw new ResponseTextException(sprintf('"%s" element id was not found in the menu', $dropdownItem), $this->getSession());
        }

        $subElement->click();
    }

    /**
     * @Given /^I should see (\d+) rows in the table$/
     *
     * @param $rowCount
     *
     * @throws \Exception
     */
    public function iShouldSeeRowsInTheTable($rowCount)
    {
        $page = $this->getPage();

        $table = $page->find('css', '.vic-bordered-list');

        if (!$table) {
            throw new \Exception('Cannot find a table!');
        }

        $rows = $table->findAll('css', '.list-group-item');

        if ($rowCount != count($rows)) {
            throw new \Exception('not the right number! - I see '.count($rows));
        }
    }

    /**
     * @Given I click the :arg1 element
     *
     * @param $selector
     *
     * @throws \Exception
     */
    public function iClickTheElement($selector)
    {
        $page = $this->getPage();

        $element = $page->find('css', $selector);

        if (empty($element)) {
            throw new \Exception("No html element found for the selector ('$selector')");
        }

        $element->click();
    }

    /**
     * @When /^(?:|I )select "(?P<option>\w+)" in the "(?P<name>\w+)" select$/
     *
     * @param $option
     * @param $name
     */
    public function selectState($option, $name)
    {
        $page = $this->getPage();

        $selectElement = $page->find('xpath', '//select[@id="'.$name.'"]');

        $selectElement->selectOption($option);
    }

    /**
     * @When I select :arg1 in the :arg2 select from :arg3 form
     *
     * @param string $option
     * @param string $name
     * @param string $form
     *
     * @throws \Exception
     */
    public function iSelectFromForm($option, $name, $form)
    {
        $page = $this->getPage();

        $formElement = $page->find('xpath', '//div[@id="'.$form.'"]/form');

        if (empty($formElement)) {
            throw new \Exception("No form found for id ('".$form."')");
        }

        $selectElement = $formElement->find('xpath', '//select[@id="'.$name.'"]');

        if (empty($selectElement)) {
            throw new \Exception("No select found for id ('".$name."')");
        }

        $selectElement->selectOption($option);
    }

    /**
     * @Then I fill in :arg1 with :arg2 from :arg3 form
     *
     * @param string $field
     * @param string $value
     * @param string $form
     *
     * @throws \Exception
     */
    public function iFillFromForm($field, $value, $form)
    {
        $page = $this->getPage();

        $formElement = $page->find('xpath', '//div[@id="'.$form.'"]/form');

        if (empty($formElement)) {
            throw new \Exception("No form found for id ('".$form."')");
        }

        $inputElement = $formElement->find('xpath', '//input[@id="'.$field.'"]');

        if (empty($inputElement)) {
            throw new \Exception("No input found for id ('".$field."')");
        }

        $inputElement->getParent()->fillField($field, $value);
    }

    /**
     * @Then I click the :arg1 element from :arg2 form
     *
     * @param string $selector
     * @param string $form
     *
     * @throws \Exception
     */
    public function iClickTheElementFromForm($selector, $form)
    {
        $page = $this->getPage();

        $formElement = $page->find('xpath', '//div[@id="'.$form.'"]/form');

        $element = $formElement->find('css', $selector);

        if (empty($element)) {
            throw new \Exception("No html element found for the selector ('$selector')");
        }

        $element->click();
    }

    /**
     * @return DocumentElement
     */
    private function getPage()
    {
        /** @var Session $session */
        $session = $this->getSession();

        return $session->getPage();
    }
}
