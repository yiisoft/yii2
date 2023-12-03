<?php

namespace yiiunit\framework\helpers;

use PHPUnit\Framework\TestCase;
use yii\helpers\BaseUrl;

/**
 * @group helpers
 */
class BaseUrlTest extends TestCase
{

    public function testIsRelativeWithAbsoluteUrlWillReturnFalse()
    {
        $this->assertFalse(BaseUrl::isRelative('https://acme.com/tnt-room=123'));
    }

    public function testUrlStartingWithDoubleSlashesWillReturnFalse()
    {
        $this->assertFalse(BaseUrl::isRelative('//example.com'));
    }

    public function testIsRelativeWithRelativeUrlWillReturnTrue()
    {
        $this->assertTrue(
            BaseUrl::isRelative('acme.com/tnt-room=123')
        );
    }

    public function testIsRelativeWithRelativeUrlHavingHttpsUrlAsParamValueWillReturnTrue()
    {
        $this->assertTrue(BaseUrl::isRelative(
            'acme.com/tnt-room-link=https://asd.com'
        ));
    }

    public function testIsRelativeWithAbsoluteUrlHavingHttpsUrlAsParamValueWillReturnFalse()
    {
        $this->assertFalse(
            BaseUrl::isRelative('https://acme.com/tnt-link=https://tnt.com')
        );
    }

    public function testIsRelativeWithA()
    {
        $this->assertTrue(
            BaseUrl::isRelative('/name=bugs.bunny')
        );
    }

    public function testIsRelativeWithFtpProtocolUrlWillReturnFalse()
    {
        $this->assertFalse(
            BaseUrl::isRelative('ftp://ftp.acme.com/tnt-suppliers.txt')
        );
    }

    public function testIsRelativeWithHttpUrl()
    {
        $this->assertFalse(
            BaseUrl::isRelative('http://no-protection.acme.com')
        );
    }

    public function testIsRelativeWithFileUrl()
    {
        $this->assertFalse(
            BaseUrl::isRelative('file:///home/User/2ndFile.html')
        );
    }

}
