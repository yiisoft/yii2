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
    /**
     * @var string[] Ordered list of line types
     */
    public $types = ['Bug', 'Enh', 'Chg'];
    public $regex = '/- (?<type>{types})( #\d+(, #\d+)*)?: .*[^.] \(.*\)$/';

    public function setUp()
    {
        $this->regex = strtr($this->regex, ['{types}' => implode('|', $this->types)]);
    }

    public function changeProvider()
    {

        $lines = explode("\n", file_get_contents(__DIR__ . '/../../framework/CHANGELOG.md'));

        // Don't check last 1500 lines, they are old and often don't obey the standard.
        $lastIndex = count($lines) - 1500;
        $result = [];
        $previous = null;
        foreach ($lines as $i => $line) {
            if (strncmp('- ', $line, 2) === 0) {
                $result[] = [$line, $previous];
            }
            $previous = $line;

            if ($i > $lastIndex) {
                break;
            }
        }
        return $result;
    }

    /**
     * @dataProvider changeProvider
     */
    public function testContributorLine($line, $previous)
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

        $this->assertRegExp($this->regex, $line);


        if ($previous !== false
            && preg_match($this->regex, $previous, $matches)
            && isset($matches['type'])
        ) {
            $previousType = $matches['type'];
            preg_match($this->regex, $line, $matches);
            $type = $matches['type'];
            $this->assertGreaterThanOrEqual(array_search($previousType, $this->types), array_search($type, $this->types));
        }
    }


}
