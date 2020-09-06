<?php


namespace yiiunit\framework\web\session;

use yii\web\Session;

trait SessionTestTrait
{
    /**
     * @param string $class
     */
    protected function useStrictModeTest($class)
    {
        //non-strict-mode test
        /** @var Session $nonStrictSession */
        $nonStrictSession = new $class([
            'useStrictMode' => false,
        ]);
        $nonStrictSession->close();
        $nonStrictSession->destroySession('non-existing-non-strict');
        $nonStrictSession->setId('non-existing-non-strict');
        $nonStrictSession->open();
        $this->assertEquals('non-existing-non-strict', $nonStrictSession->getId());
        $nonStrictSession->close();

        //strict-mode test
        /** @var Session $strictSession */
        $strictSession = new $class([
            'useStrictMode' => true,
        ]);
        $strictSession->close();
        $strictSession->destroySession('non-existing-strict');
        $strictSession->setId('non-existing-strict');
        $strictSession->open();
        $id = $strictSession->getId();
        $this->assertNotEquals('non-existing-strict', $id);
        $strictSession->set('strict_mode_test', 'session data');
        $strictSession->close();
        //Ensure session was not stored under forced id
        $strictSession->setId('non-existing-strict');
        $strictSession->open();
        $this->assertNotEquals('session data', $strictSession->get('strict_mode_test'));
        $strictSession->close();
        //Ensure session can be accessed with the new (and thus existing) id.
        $strictSession->setId($id);
        $strictSession->open();
        $this->assertNotEmpty($id);
        $this->assertEquals($id, $strictSession->getId());
        $this->assertEquals('session data', $strictSession->get('strict_mode_test'));
        $strictSession->close();
    }
}
