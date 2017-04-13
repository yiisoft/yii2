<?php
/*
 * Ensures compatibility with PHPUnit < 6.x
 */

namespace PHPUnit\Framework\Constraint {
    if (!class_exists('PHPUnit\Framework\Constraint\Constraint') && class_exists('PHPUnit_Framework_Constraint')) {
        abstract class Constraint extends \PHPUnit_Framework_Constraint {}
    }
}

namespace PHPUnit\Framework {
    if (!class_exists('PHPUnit\Framework\TestCase') && class_exists('PHPUnit_Framework_TestCase')) {
        abstract class TestCase extends \PHPUnit_Framework_TestCase {
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
                $this->setExpectedException($this->getExpectedException(), $message);
            }
        }
    }
}
