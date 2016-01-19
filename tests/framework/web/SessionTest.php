<?php

namespace yiiunit\framework\web;

use yii\web\Session;
use yiiunit\TestCase;

/**
 * @group web
 */
class SessionTest extends TestCase
{
    /**
     * Test to prove that after Session::destroy session id set to old value
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
}
