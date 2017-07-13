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

        $expected = <<<'EXPECTED'
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
            ])->setScreenWidth(200)->run()
        );
    }

    public function testTableWithFullwidthChars()
    {
        $table = new Table();

        // test fullwidth chars
        // @see https://en.wikipedia.org/wiki/Halfwidth_and_fullwidth_forms
        $expected = <<<'EXPECTED'
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
            ])->setScreenWidth(200)->run()
        );
    }

    public function testLists()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
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
            ])->setScreenWidth(200)->run()
        );
    }

    public function testListPrefix()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
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
                ['testcontent21', 'testcontent22', ['col1', 'col2']],
            ])->setScreenWidth(200)->setListPrefix('* ')->run()
        );
    }

    public function testCustomChars()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
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
                Table::CHAR_TOP => '+', Table::CHAR_TOP_MID => '*', Table::CHAR_TOP_LEFT => '*',
                Table::CHAR_TOP_RIGHT => '*', Table::CHAR_BOTTOM => '+', Table::CHAR_BOTTOM_MID => '*',
                Table::CHAR_BOTTOM_LEFT => '*', Table::CHAR_BOTTOM_RIGHT => '*', Table::CHAR_LEFT => '/',
                Table::CHAR_LEFT_MID => '*', Table::CHAR_MID => '+', Table::CHAR_MID_MID => '*',
                Table::CHAR_RIGHT => '/', Table::CHAR_RIGHT_MID => '*', Table::CHAR_MIDDLE => '/',
            ])->setScreenWidth(200)->run()
        );
    }

    public function testTableWidgetSyntax()
    {
        $expected = <<<'EXPECTED'
╔═══════════════╤═══════════════╤═══════════════╗
║ test1         │ test2         │ test3         ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent1  │ testcontent2  │ testcontent3  ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent21 │ testcontent22 │ testcontent23 ║
╚═══════════════╧═══════════════╧═══════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE(
            $expected,
            Table::widget([
                'headers' => ['test1', 'test2', 'test3'],
                'rows' => [
                    ['testcontent1', 'testcontent2', 'testcontent3'],
                    ['testcontent21', 'testcontent22', 'testcontent23'],
                ],
                'screenWidth' => 200,
            ])
        );
    }
}
