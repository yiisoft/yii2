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
        $nonStrictSession->destroySession('non_existing_non_strict');
        $nonStrictSession->setId('non_existing_non_strict');
        $nonStrictSession->open();
        $this->assertEquals('non_existing_non_strict', $nonStrictSession->getId());
        $nonStrictSession->close();

        //strict-mode test
        /** @var Session $strictSession */
        $strictSession = new $class([
            'useStrictMode' => true,
        ]);
        $strictSession->destroySession('non_existing_strict');
        $strictSession->setId('non_existing_strict');
        $strictSession->open();
        $id = $strictSession->getId();
        $this->assertNotEquals('non_existing_strict', $id);
        $strictSession->set('strict_mode_test', 'session data');
        $strictSession->close();
        //Ensure session was not stored under forced id
        $strictSession->setId('non_existing_strict');
        $strictSession->open();
        $this->assertNotEquals('session data', $strictSession->get('strict_mode_test'));
        $strictSession->close();
        //Ensure session can be accessed with the new (and thus existing) id.
        $strictSession->setId($id);
        $strictSession->open();
        $this->assertEquals($id, $strictSession->getId());
        $this->assertEquals('session data', $strictSession->get('strict_mode_test'));
        $strictSession->close();
    }
}
