<?php

namespace Victoire\Tests\Features\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\Selenium2Driver;
use Knp\FriendlyContexts\Context\RawMinkContext;

class JavascriptContext extends RawMinkContext
{
    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function maximizeWindow(BeforeScenarioScope $scope)
    {
        if (($this->getSession()->getDriver() instanceof Selenium2Driver)) {
            $this->iMaximizeTheWindow();
        }
    }

    /**
     * @When /^I maximize the window/
     */
    public function iMaximizeTheWindow()
    {
        //the window got with maximizeWindow native function is too small
        $this->getSession()->resizeWindow(1600, 1200, 'current');
    }

    /**
     * @When /^I minimize the window/
     */
    public function iMinimizeTheWindow()
    {
        $this->getSession()->resizeWindow(500, 500, 'current');
    }

    /**
     * @When /^I resize the window to (\d+)x(\d+)/
     */
    public function iResizeTheWindow($width, $height)
    {
        $this->getSession()->resizeWindow(intval($width), intval($height), 'current');
    }
}
