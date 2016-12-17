<?php

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
    }

    /**
     * @dataProvider provider
     */
    public function testResolve($params, $expected)
    {
        $request = new Request();

        $request->setParams($params);
        list($route, $params) = $request->resolve();
        $this->assertEquals($expected['route'], $route);
        $this->assertEquals($expected['params'], $params);
    }
}
