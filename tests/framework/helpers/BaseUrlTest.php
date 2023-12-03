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

    public function testEnsureSchemeWithRelativeUrlWillReturnInputUrl()
    {
        $url = 'acme.com?name=bugs.bunny';
        $this->assertEquals('acme.com?name=bugs.bunny', BaseUrl::ensureScheme($url, 'https'));
    }

    public function testEnsureSchemeWithRelativeUrlWithAnotherUrlAsParamWillReturnInputUrl()
    {
        $this->assertEquals('acme.com/test?tnt-link=https://tnt.com/',
            BaseUrl::ensureScheme('acme.com/test?tnt-link=https://tnt.com/', 'https')
        );
    }

    public function testEnsureSchemeWithSchemeNotAStringWillReturnInputUrl()
    {
        $url = 'acme.com?name=bugs.bunny';
        $this->assertEquals('acme.com?name=bugs.bunny', BaseUrl::ensureScheme($url, 123));
    }

    public function testEnsureSchemeWithProtocolRelativeUrlAndHttpsSchemeWillBeNormalized()
    {
        $url = '//acme.com?characters/list';
        $this->assertEquals('https://acme.com?characters/list', BaseUrl::ensureScheme($url, 'https'));
    }

    public function testEnsureSchemeWithProtocolRelativeUrlAndEmptySchemeWillBeReturned()
    {
        $url = '//acme.com?characters/list';
        $this->assertEquals('//acme.com?characters/list', BaseUrl::ensureScheme($url, ''));
    }

    public function testAbsoluteUrlProtocolAndEmptySchemeWillCreateProtocolRelativeUrl()
    {
        $url = 'https://acme.com?characters/list';
        $this->assertEquals('//acme.com?characters/list', BaseUrl::ensureScheme($url, ''));
    }

    public function testEnsureSchemeWithAbsoluteUrlWithAnotherUrlAsParamWillReturnInputUrl()
    {
        $url = 'ss://acme.com/test?tnt-link=https://tnt.com/';
        $this->assertEquals('https://acme.com/test?tnt-link=https://tnt.com/', BaseUrl::ensureScheme($url, 'https'));
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
