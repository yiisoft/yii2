<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yii\data\Pagination;
use yii\helpers\StringHelper;
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

    /**
     * Get pagination.
     * @param int $page
     * @return Pagination
     */
    private function getPagination($page)
    {
        $pagination = new Pagination();
        $pagination->setPage($page);
        $pagination->totalCount = 500;
        $pagination->route = 'test';

        return $pagination;
    }

    public function testFirstLastPageLabels()
    {
        $pagination = $this->getPagination(5);
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
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(0),
            'disabledListItemSubTagOptions' => ['class' => 'foo-bar'],
        ]);

        static::assertContains('<span class="foo-bar">&laquo;</span>', $output);
    }

    public function testDisabledPageElementOptionsWithTagOption()
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(0),
            'disabledListItemSubTagOptions' => ['class' => 'foo-bar', 'tag' => 'div'],
        ]);

        static::assertContains('<div class="foo-bar">&laquo;</div>', $output);
    }

    public function testDisableCurrentPageButton()
    {
        $pagination = $this->getPagination(5);
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

    public function testOptionsWithTagOption()
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(5),
            'options' => [
                'tag' => 'div',
            ],
        ]);

        $this->assertTrue(StringHelper::startsWith($output, '<div>'));
        $this->assertTrue(StringHelper::endsWith($output, '</div>'));
    }

    public function testLinkWrapOptions()
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(1),
            'linkContainerOptions' => [
                'tag' => 'div',
                'class' => 'my-class',
            ],
        ]);

        $this->assertContains(
            '<div class="my-class"><a href="/?r=test&amp;page=3" data-page="2">3</a></div>',
            $output
        );
        $this->assertContains(
            '<div class="my-class active"><a href="/?r=test&amp;page=2" data-page="1">2</a></div>',
            $output
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent()
    {
        $initTriggered = false;
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(1),
            'on init' => function () use (&$initTriggered) {
                $initTriggered = true;
            }
        ]);

        $this->assertTrue($initTriggered);
    }
}
