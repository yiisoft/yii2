<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\base\BaseObject;
use yii\web\Link;
use yiiunit\TestCase;

/**
 * @group web
 */
class LinkTest extends TestCase
{
    public function testSerializeSimpleArraySuccessfully()
    {
        $this->assertEquals([
            'link' => [
                'href' => 'http://example.com/users/4'
            ],
            'title' => [
                'href' => 'My user',
            ]
        ], Link::serialize([
            'link' => 'http://example.com/users/4',
            'title' => 'My user',
        ]));
    }

    public function testSerializeArrayWithLinkSuccessfully()
    {
        $link = new UserLink([
            'link' => 'http://example.com/users/4',
            'title' => 'User 4',
        ]);

        $this->assertEquals([
            'link serialized' => [
                'title' => 'User 4',
                'link' => 'http://example.com/users/4'
            ],
            'title' => ['href' => 'My user'],
        ], Link::serialize([
            'link serialized' => $link,
            'title' => 'My user',
        ]));
    }

    public function testSerializeNestedArrayWithLinkSuccessfully()
    {
        $link = new UserLink([
            'link' => 'http://example.com/users/4',
            'title' => 'User 4',
        ]);

        $this->assertEquals([
            'link serialized' => [
                'href' => [
                    'manager' => [
                        'title' => 'User 4',
                        'link' => 'http://example.com/users/4'
                    ]]
            ],
            'title' => ['href' => 'My user'],
        ],
            Link::serialize([
                'link serialized' => [
                    'manager' => $link
                ],
                'title' => 'My user',
            ]));
    }

    public function testSerializeArrayWithNotaLinkClassesSuccessfully()
    {
        $notALink = new NotALink([
            'fakeName' => 'John',
        ]);

        $this->assertEquals([
            'this is not a link' => ['href' => $notALink],
        ], Link::serialize([
            'this is not a link' => $notALink,
        ]));
    }
}

class UserLink extends Link
{
    /** @var string */
    public $link;

    /** @var string */
    public $title;
}

class NotALink extends BaseObject
{
    /** @var string */
    public $fakeName;
}
