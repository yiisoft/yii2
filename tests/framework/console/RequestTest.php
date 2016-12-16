<?php

use yii\console\Request;
use yiiunit\TestCase;

/**
 * @group console
 */
class RequestTest extends TestCase
{
    public function testResolve()
    {
        $request = new Request();

        $tests = [
            [
                'params' => [
                    'controller',
                ],
                'expected' => [
                    'route' => 'controller',
                    'params' => [
                    ]
                ]
            ],
            [
                'params' => [
                    'controller/route',
                    'param1',
                    '-12345',
                    '--option1',
                    '--option2=testValue',
                    '-alias1',
                    '-alias2=testValue'
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
                            'alias2' => 'testValue'
                        ]
                    ]
                ]
            ]
        ];

        foreach ($tests as $test) {
            $request->setParams($test['params']);
            list($route, $params) = $request->resolve();
            $this->assertEquals($test['expected']['route'], $route);
            $this->assertEquals($test['expected']['params'], $params);
        }
    }
}
