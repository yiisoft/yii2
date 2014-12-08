<?php

namespace yiiunit\extensions\swiftmailer;

use Yii;
use yii\swiftmailer\Mailer;
use yiiunit\VendorTestCase;

Yii::setAlias('@yii/swiftmailer', __DIR__ . '/../../../../extensions/swiftmailer');

/**
 * @group vendor
 * @group mail
 * @group swiftmailer
 */
class MailerTest extends VendorTestCase
{
    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'email' => $this->createTestEmailComponent()
            ]
        ]);
    }

    /**
     * @return Mailer test email component instance.
     */
    protected function createTestEmailComponent()
    {
        $component = new Mailer();

        return $component;
    }

    // Tests :

    public function testSetupTransport()
    {
        $mailer = new Mailer();

        $transport = \Swift_MailTransport::newInstance();
        $mailer->setTransport($transport);
        $this->assertEquals($transport, $mailer->getTransport(), 'Unable to setup transport!');
    }

    /**
     * @depends testSetupTransport
     */
    public function testConfigureTransport()
    {
        $mailer = new Mailer();

        $transportConfig = [
            'class' => 'Swift_SmtpTransport',
            'host' => 'localhost',
            'username' => 'username',
            'password' => 'password',
        ];
        $mailer->setTransport($transportConfig);
        $transport = $mailer->getTransport();
        $this->assertTrue(is_object($transport), 'Unable to setup transport via config!');
        $this->assertEquals($transportConfig['class'], get_class($transport), 'Invalid transport class!');
        $this->assertEquals($transportConfig['host'], $transport->getHost(), 'Invalid transport host!');
    }

    /**
     * @depends testConfigureTransport
     */
    public function testConfigureTransportConstruct()
    {
        $mailer = new Mailer();

        $class = 'Swift_SmtpTransport';
        $host = 'some.test.host';
        $port = 999;
        $transportConfig = [
            'class' => $class,
            'constructArgs' => [
                $host,
                $port,
            ],
        ];
        $mailer->setTransport($transportConfig);
        $transport = $mailer->getTransport();
        $this->assertTrue(is_object($transport), 'Unable to setup transport via config!');
        $this->assertEquals($class, get_class($transport), 'Invalid transport class!');
        $this->assertEquals($host, $transport->getHost(), 'Invalid transport host!');
        $this->assertEquals($port, $transport->getPort(), 'Invalid transport host!');
    }

    /**
     * @depends testConfigureTransportConstruct
     */
    public function testConfigureTransportWithPlugins()
    {
        $mailer = new Mailer();

        $pluginClass = 'Swift_Plugins_ThrottlerPlugin';
        $rate = 10;

        $transportConfig = [
            'class' => 'Swift_SmtpTransport',
            'plugins' => [
                [
                    'class' => $pluginClass,
                    'constructArgs' => [
                        $rate,
                    ],
                ],
            ],
        ];
        $mailer->setTransport($transportConfig);
        $transport = $mailer->getTransport();
        $this->assertTrue(is_object($transport), 'Unable to setup transport via config!');
        $this->assertContains(':' . $pluginClass . ':', print_r($transport, true), 'Plugin not added');
    }

    public function testGetSwiftMailer()
    {
        $mailer = new Mailer();
        $this->assertTrue(is_object($mailer->getSwiftMailer()), 'Unable to get Swift mailer instance!');
    }
}
