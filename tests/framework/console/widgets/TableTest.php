<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use yii\console\widgets\Table;
use yiiunit\TestCase;

/**
 * @group console
 */
class TableTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testTable()
    {
        $table = new Table();

        $expected = <<<EXPECTED
╔═══════════════╤═══════════════╤═══════════════╗
║ test1         │ test2         │ test3         ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent1  │ testcontent2  │ testcontent3  ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent21 │ testcontent22 │ testcontent23 ║
╚═══════════════╧═══════════════╧═══════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([
                ['testcontent1', 'testcontent2', 'testcontent3'],
                ['testcontent21', 'testcontent22', 'testcontent23'],
            ])->setScreenSize(200)->render()
        );
    }

    public function testTableWithFullwidthChars()
    {
        $table = new Table();

        // test fulwidth chars
        // @see https://en.wikipedia.org/wiki/Halfwidth_and_fullwidth_forms
        $expected = <<<EXPECTED
╔═════════════════╤═════════════════╤═════════════════╗
║ test1           │ test2           │ ｔｅｓｔ３      ║
╟─────────────────┼─────────────────┼─────────────────╢
║ testcontent1    │ testcontent2    │ testcontent3    ║
╟─────────────────┼─────────────────┼─────────────────╢
║ testcontent２１ │ testcontent２２ │ testcontent２３ ║
╚═════════════════╧═════════════════╧═════════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'ｔｅｓｔ３'])
            ->setRows([
                ['testcontent1', 'testcontent2', 'testcontent3'],
                ['testcontent２１', 'testcontent２２', 'testcontent２３'],
            ])->setScreenSize(200)->render()
        );
    }

    public function testLists()
    {
        $table = new Table();

        $expected = <<<EXPECTED
╔═══════════════╤═══════════════╤══════════════╗
║ test1         │ test2         │ test3        ║
╟───────────────┼───────────────┼──────────────╢
║ testcontent1  │ testcontent2  │ testcontent3 ║
╟───────────────┼───────────────┼──────────────╢
║ testcontent21 │ testcontent22 │ • col1       ║
║               │               │ • col2       ║
╚═══════════════╧═══════════════╧══════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([
                ['testcontent1', 'testcontent2', 'testcontent3'],
                ['testcontent21', 'testcontent22', ['col1', 'col2']],
            ])->setScreenSize(200)->render()
        );
    }

    public function testListPrefix()
    {
        $table = new Table();

        $expected = <<<EXPECTED
╔═══════════════╤═══════════════╤══════════════╗
║ test1         │ test2         │ test3        ║
╟───────────────┼───────────────┼──────────────╢
║ testcontent1  │ testcontent2  │ testcontent3 ║
╟───────────────┼───────────────┼──────────────╢
║ testcontent21 │ testcontent22 │ * col1       ║
║               │               │ * col2       ║
╚═══════════════╧═══════════════╧══════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([
                ['testcontent1', 'testcontent2', 'testcontent3'],
                ['testcontent21', 'testcontent22', ['col1', 'col2']]
            ])->setScreenSize(200)->setListPrefix('* ')->render()
        );
    }

    public function testCustomChars()
    {
        $table = new Table();

        $expected = <<<EXPECTED
*++++++++++++++++*+++++++++++++++++*++++++++++++++++++*
/ test1          / test2           / test3            /
*++++++++++++++++*+++++++++++++++++*++++++++++++++++++*
/ testcontent1   / testcontent2    / testcontent3     /
*++++++++++++++++*+++++++++++++++++*++++++++++++++++++*
/ testcontent_21 / testcontent__22 / testcontent___23 /
*++++++++++++++++*+++++++++++++++++*++++++++++++++++++*

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([
                ['testcontent1', 'testcontent2', 'testcontent3'],
                ['testcontent_21', 'testcontent__22', 'testcontent___23'],
            ])->setChars([
                'top' => '+', 'top-mid' => '*', 'top-left' => '*',
                'top-right' => '*', 'bottom' => '+', 'bottom-mid' => '*',
                'bottom-left' => '*', 'bottom-right' => '*', 'left' => '/',
                'left-mid' => '*', 'mid' => '+', 'mid-mid' => '*',
                'right' => '/', 'right-mid' => '*', 'middle' => '/',
            ])->setScreenSize(200)->render()
        );
    }
}

