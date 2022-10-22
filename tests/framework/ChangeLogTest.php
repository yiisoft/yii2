<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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

        $lines = preg_split("~\R~", file_get_contents(__DIR__ . '/../../framework/CHANGELOG.md'), -1, PREG_SPLIT_NO_EMPTY);

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
        if ($line === '- no changes in this release.') {
            $this->markTestSkipped('Placeholder line');
        }

        /**
         * Each change line is tested for:
         * - Starts with "- "
         * - Has a type: Bug, Enh, Chg, New
         * - Has a number formatted like #12345 one or more times
         * - Can contain CVE ID
         * - Description starts after ": "
         * - Description ends without a "."
         * - Line ends with contributor name between "(" and ")".
         */
        $this->assertRegExp('/- (Bug|Enh|Chg|New)( #\d+(, #\d+)*)?(\s\(CVE-[\d-]+\))?: .*[^.] \(.+\)$/', $line);
    }
}
