<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session;

use yii\web\Session;
use yiiunit\TestCase;

/**
 * @group web
 */
class SessionTest extends TestCase
{
    /**
     * Test to prove that after Session::destroy session id set to old value.
     */
    public function testDestroySessionId()
    {
        $session = new Session();
        $session->open();
        $oldSessionId = @session_id();

        $this->assertNotEmpty($oldSessionId);

        $session->destroy();

        $newSessionId = @session_id();
        $this->assertNotEmpty($newSessionId);
        $this->assertEquals($oldSessionId, $newSessionId);
    }

    /**
     * Test to prove that after Session::open changing session parameters will not throw exceptions
     * and its values will be changed as expected.
     */
    public function testParamsAfterSessionStart()
    {
        $session = new Session();
        $session->open();

        $oldUseTransparentSession = $session->getUseTransparentSessionID();
        $session->setUseTransparentSessionID(true);
        $newUseTransparentSession = $session->getUseTransparentSessionID();
        $this->assertNotEquals($oldUseTransparentSession, $newUseTransparentSession);
        $this->assertTrue($newUseTransparentSession);
        //without this line phpunit will complain about risky tests due to unclosed buffer
        $session->setUseTransparentSessionID(false);

        $oldTimeout = $session->getTimeout();
        $session->setTimeout(600);
        $newTimeout = $session->getTimeout();
        $this->assertNotEquals($oldTimeout, $newTimeout);
        $this->assertEquals(600, $newTimeout);

        $oldUseCookies = $session->getUseCookies();
        $session->setUseCookies(false);
        $newUseCookies = $session->getUseCookies();
        if (null !== $newUseCookies) {
            $this->assertNotEquals($oldUseCookies, $newUseCookies);
            $this->assertFalse($newUseCookies);
        }

        $oldGcProbability = $session->getGCProbability();
        $session->setGCProbability(100);
        $newGcProbability = $session->getGCProbability();
        $this->assertNotEquals($oldGcProbability, $newGcProbability);
        $this->assertEquals(100, $newGcProbability);
    }

    /**
     * Test set name. Also check set name twice and after open
     */
    public function testSetName()
    {
        $session = new Session();
        $session->setName('oldName');

        $this->assertEquals('oldName', $session->getName());

        $session->open();
        $session->setName('newName');

        $this->assertEquals('newName', $session->getName());

        $session->destroy();
    }
}
