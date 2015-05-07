<?php

namespace Victoire\Tests\Features\Context\SubContext;

trait JavascriptSubContextTrait
{
    /**
     * @When /^I maximize the browser$/
     */
    public function iMaximizeTheBroswer()
    {
        $this->getSession()->getDriver()->resizeWindow(1600, 1200, 'current');
    }

    /**
     * @When /^I minimize the browser$/
     */
    public function iMinimizeTheBroswer()
    {
        $this->getSession()->getDriver()->resizeWindow(1200, 600, 'current');
    }
}
