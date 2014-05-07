<?php
namespace Victoire\Bundle\CoreBundle\Features\Context;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Step;

use Symfony\Component\HttpKernel\HttpKernelInterface;

use Behat\Symfony2Extension\Context\KernelDictionary;

class PageContext extends BehatContext
{

    const PATH_PAGE_SHOW             = '/{slug}';

    const PATH_DEFAULT_LOGOUT        = '/logout';
    const PATH_DEFAULT_LOGIN         = '/login';
    const PATH_DEFAULT_HOME      = '/';

    /**
     * Constructor
     */
    public function __construct($a) {}

    /**
     * @Given /^I am on the "([^"]*)" page$/
     *
     * ex: When I am on the "alert" page
     */
    public function iAmOnTheGivenPage($page)
    {
        return new Step\Given(sprintf('I am on "%s"', $this->getPageUrl("default", $page)));
    }

    /**
     * @Then /^I should be on the "([^"]*)" page$/
     */
    public function iShouldBeOnTheGivenPage($page)
    {
        return new Step\Then(sprintf('I should be on "%s"', $this->getPageUrl("default", $page)));
    }

    /**
     * @Given /^I am on the "([^"]*)" "([^"]*)" page$/
     */
    public function iAmOnThePage($type, $action)
    {
        return new Step\Given(sprintf('I am on "%s"', $this->getPageUrl($type, $action)));
    }

    /**
     * @Then /^I should be on the "([^"]*)" "([^"]*)" page$/
     */
    public function iShouldBeOnThePage($type, $action)
    {
        return new Step\Then(sprintf('I should be on "%s"', $this->getPageUrl($type, $action)));
    }


    /**
     * @Given /^I am on the "([^"]*)" "([^"]*)" "([^"]*)" page$/
     *
     * ex: When I am on the "consultant" "firstname-lastname" "edit" page
     * ex: When I am on the "client" "company-name" "edit" page
     */
    public function iAmOnTheUserActionPage($type, $slug, $action)
    {
        return new Step\Given(sprintf('I am on "%s"', preg_replace('/{slug}/', $slug, $this->getPageUrl($type, $action))));
    }

    /**
     * @Then /^I should be on the "([^"]*)" "([^"]*)" "([^"]*)" page$/
     *
     * ex: Then I should be on the "consultant" "firstname-lastname" "edit" page
     * ex: Then I should be on the "client" "company-name" "edit" page
     */
    public function iShouldBeOnTheUserActionPage($type, $slug, $action)
    {
        return new Step\Then(sprintf('I should be on "%s"', preg_replace('/{slug}/', $slug, $this->getPageUrl($type, $action))));
    }


    /**
     * get page url
     *
     * @return string
     **/
    protected function getPageUrl($type, $action)
    {
        $base_path = constant('self::PATH_'.strtoupper($type).'_'.strtoupper($action));

        return $base_path;
    }


    /**
     * @Given /^I am on the "([^"]*)" directory page$/
     */
    public function iAmOnTheDirectoryPage($type)
    {
        return new Step\Given(sprintf('I am on "%s"', $this->getPageUrl($type, "directory")));
    }

    /**
     * @Then /^I should be on the "([^"]*)" directory page$/
     */
    public function iShouldBeOnTheDirectoryPage($type)
    {
        return new Step\Then(sprintf('I should be on "%s"', $this->getPageUrl($type, "directory")));
    }

    /**
     * @Given /^I log out$/
     */
    public function iLogOut()
    {
        return new Step\Given(sprintf('I go to "%s"', self::PATH_DEFAULT_LOGOUT));
    }
    /**
     * @Given /^I log in$/
     */
    public function iLogIn()
    {
        return new Step\Given(sprintf('I go to "%s"', self::PATH_DEFAULT_LOGIN));
    }

    /**
     * @Then /^I should be on the login page$/
     */
    public function iShouldBeOnLoginPage()
    {
        return new Step\Then(sprintf('I should be on "%s"', self::PATH_DEFAULT_LOGIN));
    }

}
