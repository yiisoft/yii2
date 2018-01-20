<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\JsonValidator;
use yiiunit\TestCase;

/**
 * @group validators
 */
class JsonValidatorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Destroy an application, validator must work without `Yii::$app`.
        $this->destroyApplication();
    }

    /**
     * @dataProvider dataProviderValidateValue
     *
     * @param mixed $value
     * @param bool $isValidExpected
     * @param string $errorExpected
     */
    public function testValidateValue($value, $isValidExpected, $errorExpected)
    {
        $validator = new JsonValidator();
        $errorActual = null;

        $isValidActual = $validator->validate($value, $errorActual);

        $this->assertEquals($isValidExpected, $isValidActual);
        $this->assertEquals($errorExpected, $errorActual);
    }

    /**
     * @return array
     */
    public function dataProviderValidateValue()
    {
        return [
            // Empty string.
            [
                '""',
                true,
                null,
            ],
            [
                '',
                false,
                'the input value must be a valid JSON string.',
            ],

            // Null.
            [
                'null',
                true,
                null,
            ],
            [
                null,
                false,
                'the input value must be a valid JSON string.',
            ],

            // Boolean.
            [
                'true',
                true,
                null,
            ],
            [
                'false',
                true,
                null,
            ],
            [
                true,
                false,
                'the input value must be a valid JSON string.',
            ],
            [
                false,
                false,
                'the input value must be a valid JSON string.',
            ],

            // Number.
            [
                '1',
                true,
                null,
            ],
            [
                '1.5',
                true,
                null,
            ],
            [
                1,
                false,
                'the input value must be a valid JSON string.',
            ],
            [
                1.5,
                false,
                'the input value must be a valid JSON string.',
            ],

            // String
            [
                '"foo"',
                true,
                null,
            ],
            [
                'bar',
                false,
                'the input value must be a valid JSON string.',
            ],

            // Object.
            [
                '{}',
                true,
                null,
            ],
            [
                '{"data": "valid JSON"}',
                true,
                null,
            ],
            [
                new \stdClass(),
                false,
                'the input value must be a valid JSON string.',
            ],
            [
                '{"data": "invalid JSON",}',
                false,
                'the input value must be a valid JSON string.',
            ],

            // Array.
            [
                '[]',
                true,
                null,
            ],
            [
                '["Valid", "JSON"]',
                true,
                null,
            ],
            [
                [],
                false,
                'the input value must be a valid JSON string.',
            ],
            [
                '["invalid", "JSON",]',
                false,
                'the input value must be a valid JSON string.',
            ],
        ];
    }
}
