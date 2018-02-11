<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework;

use yiiunit\TestCase;

/**
 * ChangeLogTest.
 * @group base
 */
class ChangeLogTest extends TestCase
{
    public function changeProvider()
    {

        $lines = explode("\n", file_get_contents(__DIR__ . '/../../framework/CHANGELOG.md'));

        // Don't check last 1500 lines, they are old and often don't obey the standard.
        $lastIndex = count($lines) - 1500;
        $result = [];
        foreach($lines as $i => $line) {
            if (strncmp('- ', $line, 2) === 0) {
                $result[] = [$line];
            }

            if ($i > $lastIndex) {
                break;
            }
        }
        return $result;
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
