<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yiiunit\TestCase;
use yii\data\Pagination;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

/**
 * @group widgets
 */
class LinkPagerTest extends TestCase
{
    protected function setUp(): void
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

    public function testFirstLastPageLabels(): void
    {
        $pagination = $this->getPagination(5);
        $output = LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => true,
            'lastPageLabel' => true,
        ]);

        $this->assertStringContainsString(
            '<li class="first"><a href="/?r=test&amp;page=1" data-page="0">1</a></li>',
            $output,
        );
        $this->assertStringContainsString(
            '<li class="last"><a href="/?r=test&amp;page=25" data-page="24">25</a></li>',
            $output,
        );

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => 'First',
            'lastPageLabel' => 'Last',
        ]);

        $this->assertStringContainsString(
            '<li class="first"><a href="/?r=test&amp;page=1" data-page="0">First</a></li>',
            $output,
        );
        $this->assertStringContainsString(
            '<li class="last"><a href="/?r=test&amp;page=25" data-page="24">Last</a></li>',
            $output,
        );

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'firstPageLabel' => false,
            'lastPageLabel' => false,
        ]);

        $this->assertStringNotContainsString('<li class="first">', $output);
        $this->assertStringNotContainsString('<li class="last">', $output);
    }

    public function testDisabledPageElementOptions(): void
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(0),
            'disabledListItemSubTagOptions' => ['class' => 'foo-bar'],
        ]);

        $this->assertStringContainsString('<span class="foo-bar">&laquo;</span>', $output);
    }

    public function testDisabledPageElementOptionsWithTagOption(): void
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(0),
            'disabledListItemSubTagOptions' => ['class' => 'foo-bar', 'tag' => 'div'],
        ]);

        $this->assertStringContainsString('<div class="foo-bar">&laquo;</div>', $output);
    }

    public function testDisableCurrentPageButton(): void
    {
        $pagination = $this->getPagination(5);
        $output = LinkPager::widget([
            'pagination' => $pagination,
            'disableCurrentPageButton' => false,
        ]);

        $this->assertStringContainsString(
            '<li class="active"><a href="/?r=test&amp;page=6" data-page="5">6</a></li>',
            $output,
        );

        $output = LinkPager::widget([
            'pagination' => $pagination,
            'disableCurrentPageButton' => true,
        ]);

        $this->assertStringContainsString('<li class="active disabled"><span>6</span></li>', $output);
    }

    public function testOptionsWithTagOption(): void
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

    public function testLinkWrapOptions(): void
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(1),
            'linkContainerOptions' => [
                'tag' => 'div',
                'class' => 'my-class',
            ],
        ]);

        $this->assertStringContainsString(
            '<div class="my-class"><a href="/?r=test&amp;page=3" data-page="2">3</a></div>',
            $output
        );
        $this->assertStringContainsString(
            '<div class="my-class active"><a href="/?r=test&amp;page=2" data-page="1">2</a></div>',
            $output
        );
    }

    public function testWithTwoButtons(): void
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(0),
            'maxButtonCount' => 2,
        ]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <ul class="pagination"><li class="prev disabled"><span>&laquo;</span></li>
            <li class="active"><a href="/?r=test&amp;page=1" data-page="0">1</a></li>
            <li><a href="/?r=test&amp;page=2" data-page="1">2</a></li>
            <li class="next"><a href="/?r=test&amp;page=2" data-page="1">&raquo;</a></li></ul>
            HTML,
            $output,
        );

        $output = LinkPager::widget([
            'pagination' => $this->getPagination(1),
            'maxButtonCount' => 2,
        ]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <ul class="pagination"><li class="prev"><a href="/?r=test&amp;page=1" data-page="0">&laquo;</a></li>
            <li class="active"><a href="/?r=test&amp;page=2" data-page="1">2</a></li>
            <li><a href="/?r=test&amp;page=3" data-page="2">3</a></li>
            <li class="next"><a href="/?r=test&amp;page=3" data-page="2">&raquo;</a></li></ul>
            HTML,
            $output,
        );
    }

    public function testWithOneButton(): void
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(0),
            'maxButtonCount' => 1,
        ]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <ul class="pagination"><li class="prev disabled"><span>&laquo;</span></li>
            <li class="active"><a href="/?r=test&amp;page=1" data-page="0">1</a></li>
            <li class="next"><a href="/?r=test&amp;page=2" data-page="1">&raquo;</a></li></ul>
            HTML,
            $output,
        );

        $output = LinkPager::widget([
            'pagination' => $this->getPagination(1),
            'maxButtonCount' => 1,
        ]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <ul class="pagination"><li class="prev"><a href="/?r=test&amp;page=1" data-page="0">&laquo;</a></li>
            <li class="active"><a href="/?r=test&amp;page=2" data-page="1">2</a></li>
            <li class="next"><a href="/?r=test&amp;page=3" data-page="2">&raquo;</a></li></ul>
            HTML,
            $output,
        );
    }

    public function testWithNoButtons(): void
    {
        $output = LinkPager::widget([
            'pagination' => $this->getPagination(0),
            'maxButtonCount' => 0,
        ]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <ul class="pagination"><li class="prev disabled"><span>&laquo;</span></li>
            <li class="next"><a href="/?r=test&amp;page=2" data-page="1">&raquo;</a></li></ul>
            HTML,
            $output
        );

        $output = LinkPager::widget([
            'pagination' => $this->getPagination(1),
            'maxButtonCount' => 0,
        ]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <ul class="pagination"><li class="prev"><a href="/?r=test&amp;page=1" data-page="0">&laquo;</a></li>
            <li class="next"><a href="/?r=test&amp;page=3" data-page="2">&raquo;</a></li></ul>
            HTML,
            $output
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent(): void
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
