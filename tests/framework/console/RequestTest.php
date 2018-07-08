<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\console\Request;
use yiiunit\TestCase;

/**
 * @group console
 */
class RequestTest extends TestCase
{
    public function provider()
    {
        return [
            [
                'params' => [
                    'controller',
                ],
                'expected' => [
                    'route' => 'controller',
                    'params' => [
                    ],
                ],
            ],
            [
                'params' => [
                    'controller/route',
                    'param1',
                    '-12345',
                    '--option1',
                    '--option2=testValue',
                    '--option-3=testValue',
                    '--option_4=testValue',
                    '-alias1',
                    '-alias2=testValue',
                    '-alias-3=testValue',
                    '-alias_4=testValue',
                ],
                'expected' => [
                    'route' => 'controller/route',
                    'params' => [
                        'param1',
                        '-12345',
                        'option1' => true,
                        'option2' => 'testValue',
                        'option-3' => 'testValue',
                        'option_4' => 'testValue',
                        '_aliases' => [
                            'alias1' => true,
                            'alias2' => 'testValue',
                            'alias-3' => 'testValue',
                            'alias_4' => 'testValue',
                        ],
                    ],
                ],
            ],
            [
                // Case: Special argument "End of Options" used
                'params' => [
                    'controller/route',
                    'param1',
                    '-12345',
                    '--option1',
                    '--option2=testValue',
                    '-alias1',
                    '-alias2=testValue',
                    '--', // Special argument "End of Options"
                    'param2',
                    '-54321',
                    '--option3',
                    '--', // Second `--` argument shouldn't be treated as special
                    '--option4=testValue',
                    '-alias3',
                    '-alias4=testValue',
                ],
                'expected' => [
                    'route' => 'controller/route',
                    'params' => [
                        'param1',
                        '-12345',
                        'option1' => '1',
                        'option2' => 'testValue',
                        '_aliases' => [
                            'alias1' => true,
                            'alias2' => 'testValue',
                        ],
                        'param2',
                        '-54321',
                        '--option3',
                        '--',
                        '--option4=testValue',
                        '-alias3',
                        '-alias4=testValue',
                    ],
                ],
            ],
            [
                // Case: Special argument "End of Options" placed before route
                'params' => [
                    '--', // Special argument "End of Options"
                    'controller/route',
                    'param1',
                    '-12345',
                    '--option1',
                    '--option2=testValue',
                    '-alias1',
                    '-alias2=testValue',
                ],
                'expected' => [
                    'route' => 'controller/route',
                    'params' => [
                        'param1',
                        '-12345',
                        '--option1',
                        '--option2=testValue',
                        '-alias1',
                        '-alias2=testValue',
                    ],
                ],
            ],
            // Case: `--<option> <value>` and `-<alias> <value>` syntax
            [
                'params' => [
                    'controller/route',
                    'param1',
                    '-12345',
                    '--option1',
                    '--option2',
                    'testValue1',
                    '--option-3',
                    'testValue2',
                    '--option_4',
                    'testValue3',
                    '-alias1',
                    '-alias2',
                    'testValue1',
                    '-alias-3',
                    'testValue2',
                    '-alias_4',
                    'testValue3',
                ],
                'expected' => [
                    'route' => 'controller/route',
                    'params' => [
                        'param1',
                        '-12345',
                        'option1' => true,
                        'option2' => 'testValue1',
                        'option-3' => 'testValue2',
                        'option_4' => 'testValue3',
                        '_aliases' => [
                            'alias1' => true,
                            'alias2' => 'testValue1',
                            'alias-3' => 'testValue2',
                            'alias_4' => 'testValue3',
                        ],
                    ],
                ],
            ],
            [
                // PHP does not allow variable name, starting with digit.
                // InvalidArgumentException must be thrown during request resolving:
                'params' => [
                    'controller/route',
                    '--0=test',
                    '--1=testing',
                ],
                'expected' => [],
                'exception' => [
                    '\yii\console\Exception',
                    'Parameter "0" is not valid',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider
     * @param array $params
     * @param array $expected
     * @param array|null $expectedException
     */
    public function testResolve($params, $expected, $expectedException = null)
    {
        if (isset($expectedException)) {
            $this->expectException($expectedException[0]);
            $this->expectExceptionMessage($expectedException[1]);
        }

        $request = new Request();

        $request->setParams($params);
        [$route, $params] = $request->resolve();
        $this->assertEquals($expected['route'], $route);
        $this->assertEquals($expected['params'], $params);
    }
}
