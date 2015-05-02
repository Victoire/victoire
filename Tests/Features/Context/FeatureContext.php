<?php

namespace Victoire\Tests\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Driver\KernelDriver;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Knp\FriendlyContexts\Context\RawMinkContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Feature context.
 */
class FeatureContext extends RawMinkContext implements Context, SnippetAcceptingContext, KernelAwareContext
{
    private $parameters = array();
    /**
     * @var
     */
    private $screenShotPath;
    use KernelDictionary;

    /**
     * @param string $screenShotPath
     */
    public function __construct($screenShotPath) {
	$this->screenshotsPath = $screenShotPath;
    }

    /**
     * @BeforeScenario @database&&@fixtures
     */
    public function cleanDatabaseFixtures()
    {
	$entityManager = $this->getContainer()->get('doctrine')->getManager();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->purge();

	$fixturesLoader = new \Acme\AppBundle\DataFixtures\Seeds\ORM\LoadFixtureData();
        $fixturesLoader->setContainer($this->getContainer());
        $fixturesLoader->load($entityManager);
    }

    /**
     * @Given /^I wait (\d+) second$/
     * @Given /^I wait (\d+) seconds$/
     */
    public function iWaitSeconds($nbr)
    {
        $this->getSession()->wait($nbr*1000);
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
     * Checks, that page contains specified text.
     * @param string  $text    the text to check
     * @param integer $timeout in milliseconds
     */
    public function assertPageContainsText($text, $timeout = 15000)
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
     */
    public function assertPageNotContainsText($text, $timeout = 15000)
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
     * @return null|boolean
     */
    public function assertElementContainsText($element, $text, $timeout = 15000)
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

            return $this->assertElementContainsText($element, $text, $timeout - 100);
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
    public function findAfterAjax($element, $value, $timeout = 15000)
    {
        if ($timeout <= 0) {
            //If the xpath method didn't worked (for example <div><i/> My sentence to find</div>) retry with simple search (no visibility handling... nevermind)
            $actual = $element->getText();
            $actual = preg_replace('/\s+/u', ' ', $actual);
            $regex  = '/'.preg_quote($value, '/').'/ui';

            return preg_match($regex, $actual);
        }

        // Hack to be able to get an element case insensitively by xpath method
        $alphabetLower = '"'.implode('', range('a', 'z')).'šœÿàáâãäåæçèéêëìíîïðñòóôõøùúûüýþö"';
        $alphabetUpper = '"'.implode('', range('A', 'Z')).'ŠŒŸÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕØÙÚÛÜÝÞÖ"';

        $item = $element->find('xpath', '/descendant-or-self::*[contains(translate(text(), '.$alphabetUpper.', '.$alphabetLower.'), translate("'.$value.'", '.$alphabetUpper.', '.$alphabetLower.'))]');

        if ($item) {
            return $item;
        } else {
            $this->getSession()->wait(100);

            return $this->findAfterAjax($element, $value, $timeout - 100);
        }
    }

    /**
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep(AfterStepScope $scope)
    {
	if (99 === $scope->getTestResult()->getResultCode()) {
	    $this->takeScreenshot();
	}
    }

    /**
     * @throws \Exception
     */
    private function takeScreenshot()
    {
	$driver = $this->getSession()->getDriver();
	if (!$driver instanceof Selenium2Driver) {
	    return;
	}
	$baseUrl = preg_replace('/\/app_test\.php/', '', $this->getMinkParameter('base_url'));
	$fileName = date('d-m-y') . '-' . uniqid() . '.png';

	$this->saveScreenshot($fileName, $this->screenshotsPath);
	print sprintf('Screenshot at: %s/%s', $baseUrl, $fileName);
    }

    /**
     * Save a screenshot of the current window to the file system.
     *
     * @param string $filename Desired filename, defaults to
     *                         <browser_name>_<ISO 8601 date>_<randomId>.png
     * @param string $filepath Desired filepath, defaults to
     *                         upload_tmp_dir, falls back to sys_get_temp_dir()
     */
    private function saveScreenshot($filename = null, $filepath = null)
    {
	// Under Cygwin, uniqid with more_entropy must be set to true.
	// No effect in other environments.
	$filename = $filename ?: sprintf('%s_%s_%s.%s', $this->getMinkParameter('browser_name'), date('c'), uniqid('', true), 'png');
	$filepath = $filepath ? $filepath : (ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir());
	file_put_contents($filepath . '/' . $filename, $this->getSession()->getScreenshot());
    }
}
