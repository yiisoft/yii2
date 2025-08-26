<?php

namespace yiiunit\framework\helpers;

use PHPUnit\Framework\TestCase;
use yii\helpers\BaseUrl;

/**
 * @group helpers
 */
class BaseUrlTest extends TestCase
{
    /** @dataProvider relativeTrueUrlProvider */
    public function testIsRelativeWillReturnTrue($url)
    {
        $this->assertTrue(BaseUrl::isRelative($url));
    }

    /** @dataProvider relativeFalseUrlProvider */
    public function testIsRelativeWillReturnFalse($url)
    {
        $this->assertFalse(BaseUrl::isRelative($url));
    }

    /** @dataProvider ensureSchemeUrlProvider */
    public function testEnsureScheme($url, $scheme, $expected)
    {
        $this->assertEquals($expected, BaseUrl::ensureScheme($url, $scheme));
    }

    public function ensureSchemeUrlProvider()
    {
        return [
            'relative url and https scheme will return input url' => [
                'url' => 'acme.com?name=bugs.bunny',
                'scheme' => 'https',
                'expected result' => 'acme.com?name=bugs.bunny',
            ],
            'relative url and another url as parameter will return input url' => [
                'url' => 'acme.com/test?tnt-link=https://tnt.com/',
                'scheme' => 'https',
                'expected' => 'acme.com/test?tnt-link=https://tnt.com/',
            ],
            'url with scheme not a string will return input url' => [
                'url' => 'acme.com?name=bugs.bunny',
                'scheme' => 123,
                'expected' => 'acme.com?name=bugs.bunny',
            ],
            'protocol relative url and https scheme will be processed' => [
                'url' => '//acme.com?characters/list',
                'scheme' => 'https',
                'expected' => 'https://acme.com?characters/list',
            ],
            'protocol relative url and empty scheme will be returned' => [
                'url' => '//acme.com?characters/list',
                'scheme' => '',
                'expected' => '//acme.com?characters/list',
            ],
            'absolute url and empty scheme will create protocol relative url' => [
                'url' => 'https://acme.com?characters/list',
                'scheme' => '',
                'expected' => '//acme.com?characters/list',
            ],
            'absolute url and different scheme will be processed' => [
                'url' => 'http://acme.com/test?tnt-link=https://tnt.com/',
                'scheme' => 'https',
                'expected' => 'https://acme.com/test?tnt-link=https://tnt.com/',
            ]
        ];
    }

    public function relativeTrueUrlProvider()
    {
        return [
            'url url without protocol' => [
                'url' => 'acme.com/tnt-room=123',
            ],
            'url without protocol and another url in a parameter value' => [
                'url' => 'acme.com?tnt-room-link=https://tnt.com',
            ],
            'path only' => [
                'url' => '/path',
            ],
            'path with param' => [
                'url' => '/path=/home/user',
            ],
        ];
    }

    public function relativeFalseUrlProvider()
    {
        return [
            'url with https protocol' => [
                'url' => 'https://acme.com',
            ],
            'url with https protocol and ending slash' => [
                'url' => 'https://acme.com/',
            ],
            'url with https protocol and another url as param value' => [
                'url' => 'https://acme.com?tnt-link=https://tnt.com',
            ],
            'url starting with two slashes' => [
                'url' => '//example.com',
            ],
            'url with ftp protocol' => [
                'url' => 'ftp://ftp.acme.com/tnt-suppliers.txt',
            ],
            'url with http protocol' => [
                'url' => 'http://no-protection.acme.com',
            ],
            'file url' => [
                'url' => 'file:///home/User/2ndFile.html',
            ]
        ];
    }
}
