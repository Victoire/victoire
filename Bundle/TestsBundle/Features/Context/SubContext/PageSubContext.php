<?php

namespace Victoire\Bundle\TestsBundle\Features\Context\SubContext;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step;

/**
 * Page subcontext
 */
class PageSubContext extends BehatContext
{
    const HOMEPAGE                      = "/";

    /**
     * @Given /^I am on the "([^"]*)" page$/
     *
     * ex: When I am on the "alert" page
     */
    public function iAmOnTheGivenPage($page)
    {
        return new Step\Given(sprintf('I am on "%s"', $this->getPageUrl($page, 'home')));
    }

    /**
     * Get page url
     *
     * @return string
     */
    protected function getPageUrl($type, $action)
    {
        $basePath  = constant('self::PATH_' . strtoupper($type) . '_' . strtoupper($action));

        return $basePath;
    }
}
