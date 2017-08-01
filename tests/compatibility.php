<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/*
 * Ensures compatibility with PHPUnit < 6.x
 */

namespace PHPUnit\Framework\Constraint {
    if (!class_exists('PHPUnit\Framework\Constraint\Constraint') && class_exists('PHPUnit_Framework_Constraint')) {
        abstract class Constraint extends \PHPUnit_Framework_Constraint
        {
        }
    }
}

namespace PHPUnit\Framework {
    if (!class_exists('PHPUnit\Framework\TestCase') && class_exists('PHPUnit_Framework_TestCase')) {

        echo "Applying compatibility patch for PHPUnit 6...\n";

        abstract class TestCase extends \PHPUnit_Framework_TestCase
        {
            /**
             * @param string $exception
             */
            public function expectException($exception)
            {
                $this->setExpectedException($exception);
            }

            /**
             * @param string $message
             */
            public function expectExceptionMessage($message)
            {
                $parentClassMethods = get_class_methods('PHPUnit_Framework_TestCase');
                if (in_array('expectExceptionMessage', $parentClassMethods)) {
                    parent::expectExceptionMessage($message);
                    return;
                }
                $this->setExpectedException($this->getExpectedException(), $message);
            }

            /**
             * @param string $messageRegExp
             */
            public function expectExceptionMessageRegExp($messageRegExp)
            {
                $parentClassMethods = get_class_methods('PHPUnit_Framework_TestCase');
                if (in_array('expectExceptionMessageRegExp', $parentClassMethods)) {
                    parent::expectExceptionMessageRegExp($messageRegExp);
                    return;
                }
                $this->setExpectedExceptionRegExp($this->getExpectedException(), $messageRegExp);
            }
        }
    }
}
