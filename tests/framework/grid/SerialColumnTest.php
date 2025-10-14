<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\grid;

use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\grid\SerialColumn;

/**
 * @group grid
 */
class SerialColumnTest extends \yiiunit\TestCase
{
    /**
     * @dataProvider provideRenderDataCellData
     */
    public function testRenderDataCell(
        array $dataProviderConfig,
        ?string $page,
        int $index,
        string $expectedResult
    ): void {
        $this->mockWebApplication();

        Yii::$app->getRequest()->setQueryParams([
            'page' => $page,
        ]);

        $column = new SerialColumn([
            'grid' => new GridView([
                'dataProvider' => new ArrayDataProvider($dataProviderConfig),
            ]),
        ]);

        $result = $column->renderDataCell(['id' => 1], 1, $index);

        $this->assertSame($expectedResult, $result);
    }

    public static function provideRenderDataCellData(): array
    {
        return [
            [
                [],
                null,
                0,
                '<td>1</td>',
            ],
            [
                [],
                null,
                5,
                '<td>6</td>',
            ],
            [
                [
                    'pagination' => new Pagination(),
                ],
                null,
                0,
                '<td>1</td>',
            ],
            [
                [
                    'pagination' => new Pagination(),
                ],
                null,
                4,
                '<td>5</td>',
            ],
            [
                [
                    'pagination' => new Pagination([
                        'totalCount' => 20,
                        'defaultPageSize' => 10,
                    ]),
                ],
                '2',
                0,
                '<td>11</td>',
            ],
            [
                [
                    'pagination' => new Pagination([
                        'totalCount' => 20,
                        'defaultPageSize' => 10,
                    ]),
                ],
                '2',
                3,
                '<td>14</td>',
            ],
        ];
    }
}
