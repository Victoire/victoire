<?php

namespace Victoire\Tests\Features\Context;

use SebastianBergmann\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class CoverageContext implements Context
{
    /**
     * @var CodeCoverage
     */
    private static $coverage;

    /** @BeforeSuite */
    public static function setup()
    {
        if (!self::$coverage) {
            $filter = new CodeCoverage\Filter();
            #$filter->addDirectoryToBlacklist(__DIR__.'/../../../vendor');
            $filter->addDirectoryToWhitelist(__DIR__.'/../../../Bundle');
            self::$coverage = new CodeCoverage\CodeCoverage(null, $filter);
        }
    }

    /** @AfterSuite */
    public static function tearDown()
    {
        $writer = new Report\Html\Facade();
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
