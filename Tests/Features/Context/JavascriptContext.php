<?php

namespace Victoire\Tests\Features\Context;

use Knp\FriendlyContexts\Context\RawMinkContext;

class JavascriptContext extends RawMinkContext
{
    /**
     * @When /^I maximize the window/
     */
    public function iMaximizeTheBroswer()
    {
        $this->getSession()->maximizeWindow();
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
