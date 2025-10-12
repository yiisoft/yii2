<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\data\Pagination;
use yii\web\Link;
use yiiunit\TestCase;

/**
 * @group data
 */
class PaginationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication([
            'components' => [
                'urlManager' => [
                    'scriptUrl' => '/index.php',
                ],
            ],
        ]);
    }

    /**
     * Data provider for [[testCreateUrl()]].
     * @return array test data
     */
    public static function dataProviderCreateUrl(): array
    {
        return [
            [
                2,
                null,
                '/index.php?r=item%2Flist&page=3',
                null,
            ],
            [
                2,
                5,
                '/index.php?r=item%2Flist&page=3&per-page=5',
                null,
            ],
            [
                2,
                null,
                '/index.php?r=item%2Flist&q=test&page=3',
                ['q' => 'test'],
            ],
            [
                2,
                5,
                '/index.php?r=item%2Flist&q=test&page=3&per-page=5',
                ['q' => 'test'],
            ],
            [
                1,
                10,
                '/index.php?r=item%2Flist&page=2&per-page=10',
                null,
                true,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCreateUrl
     *
     * @param int $page Page number.
     * @param int|null $pageSize Page size.
     * @param string $expectedUrl Expected URL.
     * @param array|null $params Route params.
     * @param bool $absolute Whether to create absolute URL.
     */
    public function testCreateUrl(
        int $page,
        int|null $pageSize,
        string $expectedUrl,
        array|null $params,
        bool $absolute = false
    ): void {
        $pagination = new Pagination();
        $pagination->route = 'item/list';
        $pagination->params = $params;
        $this->assertEquals($expectedUrl, $pagination->createUrl($page, $pageSize, $absolute));
    }

    /**
     * @depends testCreateUrl
     */
    public function testForcePageParam(): void
    {
        $pagination = new Pagination();
        $pagination->route = 'item/list';

        $pagination->forcePageParam = true;
        $this->assertEquals('/index.php?r=item%2Flist&page=1', $pagination->createUrl(0));

        $pagination->forcePageParam = false;
        $this->assertEquals('/index.php?r=item%2Flist', $pagination->createUrl(0));
    }

    public function testValidatePage(): void
    {
        $pagination = new Pagination();
        $pagination->validatePage = true;
        $pagination->pageSize = 10;
        $pagination->totalCount = 100;

        $pagination->setPage(999, true);
        $this->assertEquals(9, $pagination->getPage());

        $pagination->setPage(999, false);
        $this->assertEquals(999, $pagination->getPage());
    }

    public static function dataProviderPageCount(): array
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [-1, 0, 0],
            [-1, 1, 1],
            [1, -1, 0],
            [1, 0, 0],
            [1, 1, 1],
            [10, 10, 1],
            [10, 20, 2],
            [2, 15, 8],
        ];
    }

    /**
     * @dataProvider dataProviderPageCount
     *
     * @param int $pageSize Page size.
     * @param int $totalCount Total count.
     * @param int $pageCount Page count.
     */
    public function testPageCount(int $pageSize, int $totalCount, int $pageCount): void
    {
        $pagination = new Pagination();
        $pagination->setPageSize($pageSize);
        $pagination->totalCount = $totalCount;

        $this->assertEquals($pageCount, $pagination->getPageCount());
    }

    public function testGetDefaultPage(): void
    {
        $this->assertEquals(0, (new Pagination())->getPage());
    }

    public static function dataProviderSetPage(): array
    {
        return [
            [null, false, 0, null],
            [null, true, 0, null],
            [0, false, 0, 0],
            [0, true, 0, 0],
            [-1, false, 0, 0],
            [-1, true, 0, 0],
            [1, false, 0, 1],
            [1, true, 0, 0],
            [2, false, 10, 2],
            [2, true, 10, 0],
            [2, false, 40, 2],
            [2, true, 40, 1],
        ];
    }

    /**
     * @dataProvider dataProviderSetPage
     *
     * @param int|null $value Page number.
     * @param bool $validate Whether to validate page number.
     * @param int $totalCount Total count.
     * @param int|null $page Expected page number.
     */
    public function testSetPage(int|null $value, bool $validate, int $totalCount, int|null $page): void
    {
        $pagination = new Pagination();
        $pagination->totalCount = $totalCount;
        $pagination->setPage($value, $validate);

        $this->assertEquals($page, $pagination->getPage());
    }

    public static function dataProviderGetPageSize(): array
    {
        return [
            [[1, 50], 20],
            [[], 20],
            [[1], 20],
            [['a' => 1, 50], 20],
            [['a' => 1, 'b' => 50], 20],
            [[2, 10], 10],
            [[30, 100], 30],
        ];
    }

    /**
     * @dataProvider dataProviderGetPageSize
     *
     * @param array|bool $pageSizeLimit Page size limit.
     * @param int $pageSize Expected page size.
     */
    public function testGetPageSize(array|bool $pageSizeLimit, int $pageSize): void
    {
        $pagination = new Pagination();
        $pagination->pageSizeLimit = $pageSizeLimit;

        $this->assertEquals($pageSize, $pagination->getPageSize());
    }

    public static function dataProviderSetPageSize(): array
    {
        return [
            [null, false, false, 20],
            [null, true, false, 20],
            [null, false, [1, 50], 20],
            [null, true, [1, 50], 20],
            [1, false, false, 1],
            [1, true, false, 1],
            [1, false, [1, 50], 1],
            [1, true, [1, 50], 1],
            [10, false, [20, 50], 10],
            [10, true, [20, 50], 20],
            [40, false, [1, 20], 40],
            [40, true, [1, 20], 20],
        ];
    }

    /**
     * @dataProvider dataProviderSetPageSize
     *
     * @param int|null $value Page size.
     * @param bool $validate Whether to validate page size.
     * @param array|false $pageSizeLimit Page size limit.
     * @param int $pageSize Expected page size.
     */
    public function testSetPageSize(int|null $value, bool $validate, array|false $pageSizeLimit, int $pageSize): void
    {
        $pagination = new Pagination();
        $pagination->pageSizeLimit = $pageSizeLimit;
        $pagination->setPageSize($value, $validate);

        $this->assertEquals($pageSize, $pagination->getPageSize());
    }

    public static function dataProviderGetOffset(): array
    {
        return [
            [0, 0, 0],
            [0, 1, 0],
            [1, 1, 1],
            [1, 2, 2],
            [10, 2, 20],
        ];
    }

    /**
     * @dataProvider dataProviderGetOffset
     *
     * @param int $pageSize Page size.
     * @param int $page Page number.
     * @param int $offset Expected offset.
     */
    public function testGetOffset(int $pageSize, int $page, int $offset): void
    {
        $pagination = new Pagination();
        $pagination->setPageSize($pageSize);
        $pagination->setPage($page);

        $this->assertEquals($offset, $pagination->getOffset());
    }

    public static function dataProviderGetLimit(): array
    {
        return [
            [0, -1],
            [1, 1],
            [2, 2],
        ];
    }

    /**
     * @dataProvider dataProviderGetLimit
     */
    public function testGetLimit(int $pageSize, int $limit): void
    {
        $pagination = new Pagination();
        $pagination->setPageSize($pageSize);

        $this->assertEquals($limit, $pagination->getLimit());
    }

    public static function dataProviderGetLinks(): array
    {
        return [
            [0, 0, 0, '/index.php?r=list&page=1&per-page=0', null, null, null, null],
            [1, 0, 0, '/index.php?r=list&page=1&per-page=0', null, null, null, null],
            [
                0,
                0,
                1,
                '/index.php?r=list&page=1&per-page=0',
                '/index.php?r=list&page=1&per-page=0',
                '/index.php?r=list&page=1&per-page=0',
                null,
                null,
            ],
            [
                1,
                0,
                1,
                '/index.php?r=list&page=1&per-page=0',
                '/index.php?r=list&page=1&per-page=0',
                '/index.php?r=list&page=1&per-page=0',
                null,
                null,
            ],
            [
                0,
                1,
                1,
                '/index.php?r=list&page=1&per-page=1',
                '/index.php?r=list&page=1&per-page=1',
                '/index.php?r=list&page=1&per-page=1',
                null,
                null,
            ],
            [
                1,
                1,
                1,
                '/index.php?r=list&page=1&per-page=1',
                '/index.php?r=list&page=1&per-page=1',
                '/index.php?r=list&page=1&per-page=1',
                null,
                null,
            ],
            [
                0,
                5,
                10,
                '/index.php?r=list&page=1&per-page=5',
                '/index.php?r=list&page=1&per-page=5',
                '/index.php?r=list&page=2&per-page=5',
                null,
                '/index.php?r=list&page=2&per-page=5',
            ],
            [
                1,
                5,
                10,
                '/index.php?r=list&page=2&per-page=5',
                '/index.php?r=list&page=1&per-page=5',
                '/index.php?r=list&page=2&per-page=5',
                '/index.php?r=list&page=1&per-page=5',
                null,
            ],
            [
                1,
                5,
                15,
                '/index.php?r=list&page=2&per-page=5',
                '/index.php?r=list&page=1&per-page=5',
                '/index.php?r=list&page=3&per-page=5',
                '/index.php?r=list&page=1&per-page=5',
                '/index.php?r=list&page=3&per-page=5',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGetLinks
     *
     * @param int $page Page number.
     * @param int $pageSize Page size.
     * @param int $totalCount Total count.
     * @param string $self Expected self link.
     * @param string|null $first Expected first link.
     * @param string|null $last Expected last link.
     * @param string|null $prev Expected previous link.
     * @param string|null $next Expected next link.
     */
    public function testGetLinks(
        int $page,
        int $pageSize,
        int $totalCount,
        string $self,
        string|null $first,
        string|null $last,
        string|null $prev,
        string|null $next
    ): void {
        $pagination = new Pagination();
        $pagination->totalCount = $totalCount;
        $pagination->route = 'list';
        $pagination->setPageSize($pageSize);
        $pagination->setPage($page, true);

        $links = $pagination->getLinks();

        $this->assertSame($self, $links[Link::REL_SELF]);

        if ($first) {
            $this->assertSame($first, $links[Pagination::LINK_FIRST]);
        } else {
            $this->assertArrayNotHasKey(Pagination::LINK_FIRST, $links);
        }
        if ($last) {
            $this->assertSame($last, $links[Pagination::LINK_LAST]);
        } else {
            $this->assertArrayNotHasKey(Pagination::LINK_LAST, $links);
        }
        if ($prev) {
            $this->assertSame($prev, $links[Pagination::LINK_PREV]);
        } else {
            $this->assertArrayNotHasKey(Pagination::LINK_PREV, $links);
        }
        if ($next) {
            $this->assertSame($next, $links[Pagination::LINK_NEXT]);
        } else {
            $this->assertArrayNotHasKey(Pagination::LINK_NEXT, $links);
        }
    }
}
