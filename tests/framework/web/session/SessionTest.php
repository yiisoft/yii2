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
