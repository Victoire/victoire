<?php

namespace Victoire\Tests\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Symfony2Extension\Driver\KernelDriver;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Knp\FriendlyContexts\Context\RawMinkContext;

/**
 * Feature context.
 */
class FeatureContext extends RawMinkContext implements Context, SnippetAcceptingContext, KernelAwareContext
{
    use KernelDictionary;

    private $screenShotPath;

    /**
     * @param string $screenShotPath
     */
    public function __construct($screenShotPath)
    {
	$this->$screenShotPath = $screenShotPath;
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
	$fileName = date('d-m-y').'-'.uniqid().'.png';

	$this->saveScreenshot($fileName, $this->screenShotPath);
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
	file_put_contents($filepath.'/'.$filename, $this->getSession()->getScreenshot());
    }
}
