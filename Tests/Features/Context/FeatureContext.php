<?php

namespace Victoire\Tests\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Driver\KernelDriver;
use Knp\FriendlyContexts\Context\RawMinkContext;

/**
 * Feature context.
 */
class FeatureContext extends RawMinkContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^I wait (\d+) second$/
     * @Given /^I wait (\d+) seconds$/
     */
    public function iWaitSeconds($nbr)
    {
        $this->getSession()->wait($nbr * 1000);
    }

    public function getSymfonyProfile()
    {
        $driver = $this->getSession()->getDriver();
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
     * @param string $value
     */
    public function iShouldSeeCssOfWith($property, $elementId, $value)
    {
        $script = "return $('#".$elementId."').css('".$property."') === '".$value."';";
        $evaluated = $this->getSession()->evaluateScript($script);
        if (!$evaluated) {
            throw new \RuntimeException('The element with id "'.$elementId.'" and css property "'.$property.': '.$value.';" not found.');
        }
    }

    /**
     * @Then I should see background-image of :id with relative url :url
     */
    public function iShouldSeeBackgroundImageWithRelativeUrl($id, $url)
    {
        $session = $this->getSession();
        $base_url = $session->getCurrentUrl();
        $parse_url = parse_url($base_url);
        $base_url = rtrim($base_url, $parse_url['path']);
        $url = rtrim($base_url, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($url, DIRECTORY_SEPARATOR);
        $this->iShouldSeeCssOfWith('background-image', $id, 'url("'.$url.'")');
    }

    /**
     * @Then the title should be :title
     */
    public function theTitleShouldBe($title)
    {
        $element = $this->getSession()->getPage()->find(
            'xpath',
            sprintf('//title[normalize-space(text()) = "%s"]', $title)
        );

        if (null === $element) {
            $message = sprintf('"%s" is not the title of the page', $title);

            throw new \Behat\Mink\Exception\ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * @Then /^the meta "(.+)" should be set to "(#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3}))"/
     */
    public function theMetaShouldBeSet($name, $value)
    {
        $element = $this->assertSession()->elementExists(
            'css',
            sprintf('meta[name="%s"]', $name)
        );
        $actual = $element->getAttribute('content');
        $regex = '/'.preg_quote($value, '/').'/ui';
        if (!preg_match($regex, $actual)) {
            $message = sprintf(
                'The text "%s" was not found in the content attribute of the meta[name=%s]".',
                $value, $name
            );

            throw new ExpectationException($message, $this->getSession());
        }
    }

    /**
     * @AfterStep
     */
    public function printLastResponseOnError(AfterStepScope $event)
    {
        if (!$event->getTestResult()->isPassed()) {
            $this->saveDebugScreenshot();
        }
    }

    /**
     * @Then /^save screenshot$/
     */
    public function saveDebugScreenshot()
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof Selenium2Driver) {
            return;
        }

        if (!getenv('BEHAT_SCREENSHOTS')) {
            return;
        }

        $filename = microtime(true).'.png';
        $path = $this->getContainer()
                ->getParameter('kernel.root_dir').'/../behat_screenshots';

        if (!file_exists($path)) {
            mkdir($path);
        }

        $this->saveScreenshot($filename, $path);
    }
}
