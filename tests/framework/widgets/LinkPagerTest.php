<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
                    'scriptUrl' => '/',
                ],
            ],
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
            'lastPageLabel' => true,
        ]);

        static::assertContains('<li class="first"><a href="/?r=test&amp;page=1" data-page="0">1</a></li>', $output);
        static::assertContains('<li class="last"><a href="/?r=test&amp;page=25" data-page="24">25</a></li>', $output);

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => 'First',
            'lastPageLabel' => 'Last',
        ]);

        static::assertContains('<li class="first"><a href="/?r=test&amp;page=1" data-page="0">First</a></li>', $output);
        static::assertContains('<li class="last"><a href="/?r=test&amp;page=25" data-page="24">Last</a></li>', $output);

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => false,
            'lastPageLabel' => false,
        ]);

        static::assertNotContains('<li class="first">', $output);
        static::assertNotContains('<li class="last">', $output);
    }

    public function testDisabledPageElementOptions()
    {
        $pagination = new Pagination();
        $pagination->setPage(0);
        $pagination->totalCount = 50;
        $pagination->route = 'test';

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'disabledListItemSubTagOptions' => ['class' => 'foo-bar'],
        ]);

        static::assertContains('<span class="foo-bar">&laquo;</span>', $output);
    }

    public function testDisabledPageElementOptionsWithTagOption()
    {
        $pagination = new Pagination();
        $pagination->setPage(0);
        $pagination->totalCount = 50;
        $pagination->route = 'test';

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'disabledListItemSubTagOptions' => ['class' => 'foo-bar', 'tag' => 'div'],
        ]);

        static::assertContains('<div class="foo-bar">&laquo;</div>', $output);
    }

    public function testDisableCurrentPageButton()
    {
        $pagination = new Pagination();
        $pagination->setPage(5);
        $pagination->totalCount = 500;
        $pagination->route = 'test';

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'disableCurrentPageButton' => false,
        ]);

        static::assertContains('<li class="active"><a href="/?r=test&amp;page=6" data-page="5">6</a></li>', $output);

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'disableCurrentPageButton' => true,
        ]);

        static::assertContains('<li class="active disabled"><span>6</span></li>', $output);
    }
}
