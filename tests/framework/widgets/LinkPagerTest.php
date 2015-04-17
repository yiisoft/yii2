<?php

namespace yiiunit\framework\widgets;

use yii\data\Pagination;
use yii\widgets\LinkPager;

/**
 * @group widgets
 */
class LinkPagerTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'urlManager' => [
                    'scriptUrl' => '/'
                ]
            ]
        ]);
    }

    public function testFirstLastPageLabels()
    {
        $pagination = new Pagination();
        $pagination->setPage(5);
        $pagination->totalCount = 500;
        $pagination->route = 'test';

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => true,
            'lastPageLabel' => true
        ]);

        static::assertContains('<li class="first"><a href="/?r=test&amp;page=1" data-page="0">1</a></li>', $output);
        static::assertContains('<li class="last"><a href="/?r=test&amp;page=25" data-page="24">25</a></li>', $output);

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => 'First',
            'lastPageLabel' => 'Last'
        ]);

        static::assertContains('<li class="first"><a href="/?r=test&amp;page=1" data-page="0">First</a></li>', $output);
        static::assertContains('<li class="last"><a href="/?r=test&amp;page=25" data-page="24">Last</a></li>', $output);

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => false,
            'lastPageLabel' => false
        ]);

        static::assertNotContains('<li class="first">', $output);
        static::assertNotContains('<li class="last">', $output);
    }
}
