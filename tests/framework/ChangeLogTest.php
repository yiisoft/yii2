<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework;

use Yii;
use yii\BaseYii;
use yii\di\Container;
use yii\log\Logger;
use yiiunit\data\base\Singer;
use yiiunit\TestCase;

/**
 * ChangeLogTest.
 * @group base
 */
class ChangeLogTest extends TestCase
{
    public function changeLogProvider()
    {
        return array_map(function($line) {
            return [$line];
        }, explode("\n", file_get_contents(__DIR__ . '/../../framework/CHANGELOG.md')));
    }

    public function changeProvider()
    {
        return array_filter($this->changeLogProvider(), function($arguments) {
            return strncmp('- ', $arguments[0], 2) === 0;
        });
    }

    /**
     * @dataProvider changeLogProvider
     */
    public function testLineEndings($line)
    {
        $this->assertFalse(strpos($line, "\r"));
    }

    /**
     * @dataProvider changeProvider
     */
    public function testContributorLine($line)
    {
        /**
         * Each change line is tested for:
         * - Starts with "- "
         * - Has a type: Bug, Enh, Chg, New
         * - Has a number formatted like #12345
         * - Description starts after ": "
         * - Description ends without a "."
         * - Line ends with contributor name between "(" and ")".
         */
        $this->assertRegExp('/- (Bug|Enh|Chg|New)( #\d+(, #\d+)*)?: .*[^.] \(.*\)$/', $line);
    }


}
