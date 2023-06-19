<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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

namespace PHPUnit\TextUI {
    if (!class_exists('\PHPUnit\TextUI\ResultPrinter') && class_exists('PHPUnit_TextUI_ResultPrinter')) {
        class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
        {
        }
    }
}

namespace PHPUnit\Framework\Error {
    if (!class_exists('PHPUnit\Framework\Error\Notice') && class_exists('PHPUnit_Framework_Error_Notice')) {
        class Notice extends \PHPUnit_Framework_Error_Notice
        {
        }
    }
}

namespace PHPUnit\Framework {
    if (!class_exists('PHPUnit\Framework\TestCase') && class_exists('PHPUnit_Framework_TestCase')) {
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
