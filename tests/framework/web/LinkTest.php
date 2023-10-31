<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Link;
use yiiunit\TestCase;

/**
 * @group web
 */
class LinkTest extends TestCase
{
    public function testSerializeLinkInSimpleArrayWillRemoveNotSetValues()
    {
        $managerLink = new Link([
            'href' => 'https://example.com/users/4',
            'name' => 'User 4',
            'title' => 'My Manager',
        ]);

        $expected = [
            'self' => [
                'href' => 'https://example.com/users/1'
            ],
            'manager' => [
                'href' => 'https://example.com/users/4',
                'name' => 'User 4',
                'title' => 'My Manager',
            ],
        ];

        $this->assertEquals($expected, Link::serialize([
            'self' => 'https://example.com/users/1',
            'manager' => $managerLink,
        ]));
    }

    public function testSerializeNestedArrayWithLinkWillSerialize()
    {
        $linkData = [
            'self' => new Link([
                'href' => 'https://example.com/users/3',
                'name' => 'Daffy Duck',
            ]),
            'fellows' => [
                [
                    new Link([
                        'href' => 'https://example.com/users/4',
                        'name' => 'Bugs Bunny',
                    ]),
                ],
                [
                    new Link([
                        'href' => 'https://example.com/users/5',
                        'name' => 'Lola Bunny',
                    ]),
                ]
            ]
        ];

        $expected = [
            'self' => [
                'href' => 'https://example.com/users/3',
                'name' => 'Daffy Duck',
            ],
            'fellows' => [
                [
                    [
                        'href' => 'https://example.com/users/4',
                        'name' => 'Bugs Bunny',
                    ]
                ],
                [
                    [
                        'href' => 'https://example.com/users/5',
                        'name' => 'Lola Bunny',
                    ]
                ]
            ],
        ];

        $this->assertEquals($expected, Link::serialize($linkData));
    }
}
