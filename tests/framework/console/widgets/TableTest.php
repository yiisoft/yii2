<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\widgets;

use yii\console\widgets\Table;
use yii\helpers\Console;
use yiiunit\TestCase;

/**
 * @group console
 */
class TableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function getTableData()
    {
        return [
            [
                ['test1', 'test2', 'test3'],
                [
                    ['testcontent1', 'testcontent2', 'testcontent3'],
                    ['testcontent21', 'testcontent22', 'testcontent23'],
                ]
            ],
            [
                ['key1' => 'test1', 'key2' => 'test2', 'key3' => 'test3'],
                [
                    ['key1' => 'testcontent1', 'key2' => 'testcontent2', 'key3' => 'testcontent3'],
                    ['key1' => 'testcontent21', 'key2' => 'testcontent22', 'key3' => 'testcontent23'],
                ]
            ]
        ];
    }

    /**
     * @dataProvider getTableData
     */
    public function testTable($headers, $rows)
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

        $tableContent = $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->setScreenWidth(200)
            ->run();
        $this->assertEqualsWithoutLE($expected, $tableContent);
    }

    public function getMultiLineTableData()
    {
        return [
            [
                ['test1', 'test2', 'test3' . PHP_EOL . 'multiline'],
                [
                    ['test' . PHP_EOL . 'content1', 'testcontent2', 'test' . PHP_EOL . 'content3'],
                    [
                        'testcontent21',
                        'testcontent22' . PHP_EOL
                        . 'loooooooooooooooooooooooooooooooooooong' . PHP_EOL
                        . 'content',
                        'testcontent23' . PHP_EOL
                        . 'loooooooooooooooooooooooooooooooooooong content'
                    ],
                ]
            ],
            [
                ['key1' => 'test1', 'key2' => 'test2', 'key3' => 'test3' . PHP_EOL . 'multiline'],
                [
                    [
                        'key1' => 'test' . PHP_EOL . 'content1',
                        'key2' => 'testcontent2',
                        'key3' => 'test' . PHP_EOL . 'content3'
                    ],
                    [
                        'key1' => 'testcontent21',
                        'key2' => 'testcontent22' . PHP_EOL
                            . 'loooooooooooooooooooooooooooooooooooong' . PHP_EOL
                            . 'content',
                        'key3' => 'testcontent23' . PHP_EOL
                            . 'loooooooooooooooooooooooooooooooooooong content'
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider getMultiLineTableData
     */
    public function testMultiLineTable($headers, $rows)
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
╔═════════════╤═════════════════════════════════════╤═════════════════════════════════════════════╗
║ test1       │ test2                               │ test3                                       ║
║             │                                     │ multiline                                   ║
╟─────────────┼─────────────────────────────────────┼─────────────────────────────────────────────╢
║ test        │ testcontent2                        │ test                                        ║
║ content1    │                                     │ content3                                    ║
╟─────────────┼─────────────────────────────────────┼─────────────────────────────────────────────╢
║ testcontent │ testcontent22                       │ testcontent23                               ║
║ 21          │ loooooooooooooooooooooooooooooooooo │ loooooooooooooooooooooooooooooooooooong con ║
║             │ oong                                │ tent                                        ║
║             │ content                             │                                             ║
╚═════════════╧═════════════════════════════════════╧═════════════════════════════════════════════╝

EXPECTED;

        $tableContent = $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->setScreenWidth(100)
            ->run();
        $this->assertEqualsWithoutLE($expected, $tableContent);
    }

    public function getNumericTableData()
    {
        return [
            [
                [1, 2, 3],
                [
                    [1, 1.2, -1.3],
                    [-2, 2.2, 2.3],
                ]
            ],
            [
                ['key1' => 1, 'key2' => 2, 'key3' => 3],
                [
                    ['key1' => 1, 'key2' => 1.2, 'key3' => -1.3],
                    ['key1' => -2, 'key2' => 2.2, 'key3' => 2.3],
                ]
            ]
        ];
    }

    /**
     * @dataProvider getNumericTableData
     */
    public function testNumericTable($headers, $rows)
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
╔════╤═════╤══════╗
║ 1  │ 2   │ 3    ║
╟────┼─────┼──────╢
║ 1  │ 1.2 │ -1.3 ║
╟────┼─────┼──────╢
║ -2 │ 2.2 │ 2.3  ║
╚════╧═════╧══════╝

EXPECTED;

        $tableContent = $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->setScreenWidth(200)
            ->run();
        $this->assertEqualsWithoutLE($expected, $tableContent);
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
║ • col1        │ testcontent2  │ testcontent3 ║
║ • col2        │               │              ║
╟───────────────┼───────────────┼──────────────╢
║ testcontent21 │ testcontent22 │ • col1       ║
║               │               │ • col2       ║
╚═══════════════╧═══════════════╧══════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([
                [['key1' => 'col1', 'key2' => 'col2'], 'testcontent2', 'testcontent3'],
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

    public function testLongerListPrefix()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
╔═════════════════════════════════╤═════════════════════════════════╤═════════════════════════════╗
║ test1                           │ test2                           │ test3                       ║
╟─────────────────────────────────┼─────────────────────────────────┼─────────────────────────────╢
║ testcontent1                    │ testcontent2                    │ testcontent3                ║
╟─────────────────────────────────┼─────────────────────────────────┼─────────────────────────────╢
║ testcontent21 with looooooooooo │ testcontent22 with looooooooooo │ -- col1 with looooooooooooo ║
║ ooooooooooooong content         │ ooooooooooooong content         │ ooooooooooong content       ║
║                                 │                                 │ -- col2 with long content   ║
╚═════════════════════════════════╧═════════════════════════════════╧═════════════════════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([
                ['testcontent1', 'testcontent2', 'testcontent3'],
                [
                    'testcontent21 with loooooooooooooooooooooooong content',
                    'testcontent22 with loooooooooooooooooooooooong content',
                    ['col1 with loooooooooooooooooooooooong content', 'col2 with long content']
                ],
            ])->setScreenWidth(100)->setListPrefix('-- ')->run()
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

    public function testShortRow()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
╔═══════════════╤═══════════════╤═══════════════╗
║ test1         │ test2         │ test3         ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent1  │ testcontent2  │               ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent21 │ testcontent22 │               ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent31 │               │               ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent41 │               │ testcontent43 ║
╟───────────────┼───────────────┼───────────────╢
║               │               │ testcontent53 ║
╚═══════════════╧═══════════════╧═══════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([
                ['testcontent1', 'testcontent2'],
                ['testcontent21', 'testcontent22', null],
                ['testcontent31'],
                ['testcontent41', null, 'testcontent43'],
                [null, null, 'testcontent53'],
            ])->setScreenWidth(200)->run()
        );
    }

    public function testEmptyRow()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
╔═══════╤═══════╤═══════╗
║ test1 │ test2 │ test3 ║
╟───────┼───────┼───────╢
║       │       │       ║
╟───────┼───────┼───────╢
║       │       │       ║
╚═══════╧═══════╧═══════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([
                [null, null, null],
                [],
            ])->setScreenWidth(200)->run()
        );
    }

    public function testEmptyHeaders()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
╔═══════════════╤═══════════════╗
║ testcontent1  │ testcontent2  ║
╟───────────────┼───────────────╢
║ testcontent21 │ testcontent22 ║
╚═══════════════╧═══════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setRows([
            ['testcontent1', 'testcontent2'],
            ['testcontent21', 'testcontent22']
        ])->setScreenWidth(200)->run()
        );
    }

    public function testEmptyTable()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
╔═══════╤═══════╤═══════╗
║ test1 │ test2 │ test3 ║
╚═══════╧═══════╧═══════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', 'test3'])
            ->setRows([])->setScreenWidth(200)->run()
        );
    }

    public function testEmptyAndZeroTableCell()
    {
        $table = new Table();

        $expected = <<<'EXPECTED'
╔═══════╤═══════╗
║ test1 │ test2 ║
╟───────┼───────╢
║ 0     │       ║
╟───────┼───────╢
║ 0.0   │       ║
╚═══════╧═══════╝

EXPECTED;

        $this->assertEqualsWithoutLE(
            $expected,
            $table
                ->setHeaders(['test1', 'test2'])
                ->setRows([
                    ['0', []],
                    ['0.0', []],
                ])
                ->setScreenWidth(200)
                ->run()
        );
    }

    public function testColorizedInput()
    {
        $table = new Table();

        $expected = <<<"EXPECTED"
╔═══════╤═══════╤══════════╗
║ test1 │ test2 │ test3    ║
╟───────┼───────┼──────────╢
║ col1  │ \e[33mcol2\e[0m  │ col3     ║
╟───────┼───────┼──────────╢
║ col1  │ col2  │ • col3-0 ║
║       │       │ • \e[31mcol3-1\e[0m ║
║       │       │ • col3-2 ║
╚═══════╧═══════╧══════════╝

EXPECTED;

        $this->assertEqualsWithoutLE(
            $expected,
            $table
                ->setHeaders(['test1', 'test2', 'test3'])
                ->setRows([
                    ['col1', Console::renderColoredString('%ycol2%n'), 'col3'],
                    ['col1', 'col2', ['col3-0', Console::renderColoredString('%rcol3-1%n'), 'col3-2']],
                ])
                ->run()
        );
    }

    public function testColorizedInputStripsANSIMarkersInternally()
    {
        $table = new Table();

        $table
            ->setHeaders(['t1', 't2', 't3'])
            ->setRows([
                ['col1', Console::renderColoredString('%ycol2%n'), 'col3'],
                ['col1', 'col2', ['col3-0', Console::renderColoredString('%rcol3-1%n'), 'col3-2']],
            ])
            ->setScreenWidth(200)
            ->run();

        $columnWidths = $this->getInaccessibleProperty($table, 'columnWidths');

        $this->assertArrayHasKey(1, $columnWidths);
        $this->assertEquals(4+2, $columnWidths[1]);
        $this->assertArrayHasKey(2, $columnWidths);
        $this->assertEquals(8+2, $columnWidths[2]);
    }

    public function testCalculateRowHeightShouldNotThrowDivisionByZeroException()
    {
        $rows = [
            ['XXXXXX', 'XXXXXXXXXXXXXXXXXXXX', '', '', 'XXXXXXXXXXXXXXXXXX', 'X', 'XXX'],
            ['XXXXXX', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '', '', ''],
            ['XXXXXX', 'XXXXXXXXXXXXXXXXXXXXX', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '', '', '', ''],
        ];

        $table = Table::widget([
            'headers' => ['XX', 'XXXX'],
            'rows' => $rows
        ]);
        $this->assertEqualsWithoutLE($table, $table);
    }

    public function testLineBreakTableCell()
    {
        $table = new Table();

        $expected = <<<"EXPECTED"
╔══════════════════════╗
║ test                 ║
╟──────────────────────╢
║ AAAAAAAAAAAAAAAAAAAA ║
║ BBBBBBBBBBBBBBBBBBBB ║
║ CCCCC                ║
╟──────────────────────╢
║ • AAAAAAAAAAAAAAAAAA ║
║ BBBBBBB              ║
║ • CCCCCCCCCCCCCCCCCC ║
║ DDDDDDD              ║
╚══════════════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE(
            $expected,
            $table->setHeaders(['test'])
                ->setRows([
                    ['AAAAAAAAAAAAAAAAAAAABBBBBBBBBBBBBBBBBBBBCCCCC'],
                    [[
                        'AAAAAAAAAAAAAAAAAABBBBBBB',
                        'CCCCCCCCCCCCCCCCCCDDDDDDD',
                    ]],
                ])
                ->setScreenWidth(25)
                ->run()
        );
    }

    public function testColorizedLineBreakTableCell()
    {
        $table = new Table();

        $expected = <<<"EXPECTED"
╔══════════════════════╗
║ test                 ║
╟──────────────────────╢
║ \e[33mAAAAAAAAAAAAAAAAAAAA\e[0m ║
║ \e[33mBBBBBBBBBBBBBBBBBBBB\e[0m ║
║ \e[33mCCCCC\e[0m                ║
╟──────────────────────╢
║ \e[31mAAAAAAAAAAAAAAAAAAAA\e[0m ║
║ \e[32mBBBBBBBBBBBBBBBBBBBB\e[0m ║
║ \e[34mCCCCC\e[0m                ║
╟──────────────────────╢
║ • \e[31mAAAAAAAAAAAAAAAAAA\e[0m ║
║ \e[31mBBBBBBB\e[0m              ║
║ • \e[33mCCCCCCCCCCCCCCCCCC\e[0m ║
║ \e[33mDDDDDDD\e[0m              ║
╟──────────────────────╢
║ • \e[35mAAAAAAAAAAAAAAAAAA\e[0m ║
║ \e[31mBBBBBBB\e[0m              ║
║ • \e[32mCCCCCCCCCCCCCCCCCC\e[0m ║
║ \e[34mDDDDDDD\e[0m              ║
╚══════════════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE(
            $expected,
            $table->setHeaders(['test'])
                ->setRows([
                    [Console::renderColoredString('%yAAAAAAAAAAAAAAAAAAAABBBBBBBBBBBBBBBBBBBBCCCCC%n')],
                    [Console::renderColoredString('%rAAAAAAAAAAAAAAAAAAAA%gBBBBBBBBBBBBBBBBBBBB%bCCCCC%n')],
                    [[
                        Console::renderColoredString('%rAAAAAAAAAAAAAAAAAABBBBBBB%n'),
                        Console::renderColoredString('%yCCCCCCCCCCCCCCCCCCDDDDDDD%n'),
                    ]],
                    [[
                        Console::renderColoredString('%mAAAAAAAAAAAAAAAAAA%rBBBBBBB%n'),
                        Console::renderColoredString('%gCCCCCCCCCCCCCCCCCC%bDDDDDDD%n'),
                    ]],
                ])
                ->setScreenWidth(25)
                ->run()
        );
    }

    /**
     * @param $smallString
     * @dataProvider dataMinimumWidth
     */
    public function testMinimumWidth($smallString)
    {
        $bigString = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

        (new Table())
            ->setHeaders(['t1', 't2', ''])
            ->setRows([
                [$bigString, $bigString, $smallString],
            ])
            ->setScreenWidth(20)
            ->run();

        // Without exceptions
        $this->assertTrue(true);
    }

    public function dataMinimumWidth()
    {
        return [
            ['X'],
            [''],
            [['X', 'X', 'X']],
            [[]],
            [['']]
        ];
    }

    public function testTableWithAnsiFormat()
    {
        $table = new Table();

        // test fullwidth chars
        // @see https://en.wikipedia.org/wiki/Halfwidth_and_fullwidth_forms
        $expected = <<<EXPECTED
╔═══════════════╤═══════════════╤═══════════════╗
║ test1         │ test2         │ \e[31mtest3\e[0m         ║
╟───────────────┼───────────────┼───────────────╢
║ \e[34mtestcontent11\e[0m │ \e[33mtestcontent12\e[0m │ testcontent13 ║
╟───────────────┼───────────────┼───────────────╢
║ testcontent21 │ testcontent22 │ • a           ║
║               │               │ • \e[35mb\e[0m           ║
║               │               │ • \e[32mc\e[0m           ║
╚═══════════════╧═══════════════╧═══════════════╝

EXPECTED;

        $this->assertEqualsWithoutLE($expected, $table->setHeaders(['test1', 'test2', Console::ansiFormat('test3', [Console::FG_RED])])
            ->setRows([
                [Console::ansiFormat('testcontent11', [Console::FG_BLUE]), Console::ansiFormat('testcontent12', [Console::FG_YELLOW]), 'testcontent13'],
                ['testcontent21', 'testcontent22', [
                    'a',
                    Console::ansiFormat('b', [Console::FG_PURPLE]),
                    Console::ansiFormat('c', [Console::FG_GREEN]),
                ]],
            ])->setScreenWidth(200)->run()
        );
    }
}
