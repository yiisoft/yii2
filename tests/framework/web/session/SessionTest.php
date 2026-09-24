<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session;

use yii\web\Session;
use yii\web\SessionHandler;
use yiiunit\TestCase;

/**
 * @group web
 */
class SessionTest extends TestCase
{
    use SessionTestTrait;

    /**
     * Test to prove that after Session::destroy session id set to old value.
     */
    public function testDestroySessionId(): void
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
    public function testParamsAfterSessionStart(): void
    {
        $session = new Session();
        $session->open();

        $oldUseTransparentSession = $session->getUseTransparentSessionID();
        $session->setUseTransparentSessionID(true);
        $newUseTransparentSession = $session->getUseTransparentSessionID();
        if (PHP_VERSION_ID < 80400) {
            $this->assertNotEquals($oldUseTransparentSession, $newUseTransparentSession);
            $this->assertTrue($newUseTransparentSession);
        } else {
            $this->assertEquals($oldUseTransparentSession, $newUseTransparentSession);
            $this->assertFalse($newUseTransparentSession);
        }
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
        $session->setUseCookies($oldUseCookies);

        $oldGcProbability = $session->getGCProbability();
        $session->setGCProbability(100);
        $newGcProbability = $session->getGCProbability();
        $this->assertNotEquals($oldGcProbability, $newGcProbability);
        $this->assertEquals(100, $newGcProbability);
        $session->setGCProbability($oldGcProbability);
    }

    /**
     * Test set name. Also check set name twice and after open
     */
    public function testSetName(): void
    {
        $session = new Session();
        $session->setName('oldName');

        $this->assertEquals('oldName', $session->getName());

        $session->open();
        $session->setName('newName');

        $this->assertEquals('newName', $session->getName());

        $session->destroy();
    }

    public function testInitUseStrictMode(): void
    {
        $this->initStrictModeTest(Session::class);
    }

    public function testUseStrictMode(): void
    {
        //Manual garbage collection since native storage module might not support removing data via Session::destroySession()
        $sessionSavePath = session_save_path() ?: sys_get_temp_dir();
        // Only perform garbage collection if "N argument" is not used,
        // see https://www.php.net/manual/en/session.configuration.php#ini.session.save-path
        if (strpos($sessionSavePath, ';') === false) {
            foreach (['non-existing-non-strict', 'non-existing-strict'] as $sessionId) {
                @unlink($sessionSavePath . '/sess_' . $sessionId);
            }
        }

        $this->useStrictModeTest(Session::class);
    }

    public function testSessionHandlerCreatesSessionIdsInConfiguredFormat(): void
    {
        $session = new Session();

        // session ini directives can not be changed while a session is active
        $session->close();

        $length = ini_get('session.sid_length');
        $bitsPerCharacter = ini_get('session.sid_bits_per_character');

        // both directives are deprecated since PHP 8.4, but still honored
        @ini_set('session.sid_length', '26');
        @ini_set('session.sid_bits_per_character', '5');

        $handler = new SessionHandler($session);

        $id = $handler->create_sid();
        $nextId = $handler->create_sid();

        @ini_set('session.sid_length', (string) $length);
        @ini_set('session.sid_bits_per_character', (string) $bitsPerCharacter);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-v]{26}$/',
            $id,
            'Length and alphabet must follow the ini directives.',
        );
        $this->assertNotSame(
            $id,
            $nextId,
            'Each call must yield a new ID.',
        );
    }

    public function testSessionHandlerAcceptsEveryIdWithoutCustomValidation(): void
    {
        $handler = new SessionHandler(new Session());

        $this->assertTrue(
            $handler->validateId('unknown-id'),
            'Base storage must not reject any ID.',
        );
    }
}
