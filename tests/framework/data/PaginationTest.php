<?php

namespace yiiunit\framework\data;

use yii\data\Pagination;
use yiiunit\TestCase;

class PaginationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication([
            'components' => [
                'urlManager' => [
                    'scriptUrl' => '/index.php'
                ],
            ],
        ]);
    }

    /**
     * Data provider for [[testCreateUrl()]]
     * @return array test data
     */
    public function dataProviderCreateUrl()
    {
        return [
            [
                2,
                null,
                '/index.php?r=item%2Flist&page=3',
            ],
            [
                2,
                5,
                '/index.php?r=item%2Flist&page=3&per-page=5',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCreateUrl
     *
     * @param integer $page
     * @param integer $pageSize
     * @param string $expectedUrl
     */
    public function testCreateUrl($page, $pageSize, $expectedUrl)
    {
        $pagination = new Pagination();
        $pagination->route = 'item/list';
        $this->assertEquals($expectedUrl, $pagination->createUrl($page, $pageSize));
    }

    /**
     * @depends testCreateUrl
     */
    public function testForcePageParam()
    {
        $pagination = new Pagination();
        $pagination->route = 'item/list';

        $pagination->forcePageParam = true;
        $this->assertEquals('/index.php?r=item%2Flist&page=1', $pagination->createUrl(0));

        $pagination->forcePageParam = false;
        $this->assertEquals('/index.php?r=item%2Flist', $pagination->createUrl(0));
    }

    public function testValidatePage()
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
}
