<?php

namespace Victoire\Tests\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class CoverageContext implements Context
{
    /**
     * @var \PHP_CodeCoverage
     */
    private static $coverage;

    /** @BeforeSuite */
    public static function setup()
    {
        if (!self::$coverage) {
            $filter = new \PHP_CodeCoverage_Filter();
            $filter->addDirectoryToBlacklist(__DIR__.'/../../../vendor');
            $filter->addDirectoryToWhitelist(__DIR__.'/../../../Bundle');
            self::$coverage = new \PHP_CodeCoverage(null, $filter);
        }
    }

    /** @AfterSuite */
    public static function tearDown()
    {
        $writer = new \PHP_CodeCoverage_Report_HTML();
        $writer->process(self::$coverage, sys_get_temp_dir().'/Victoire/logs/coverage');
    }

    private function getCoverageKeyFromScope(BeforeScenarioScope $scope)
    {
        $name = $scope->getFeature()->getTitle().'::'.$scope->getScenario()->getTitle();

        return $name;
    }

    /**
     * @BeforeScenario
     */
    public function startCoverage(BeforeScenarioScope $scope)
    {
        self::$coverage->start($this->getCoverageKeyFromScope($scope));
    }

    /** @AfterScenario */
    public function stopCoverage()
    {
        self::$coverage->stop();
    }
}
