<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\rest;

use Yii;
use yii\rest\OptionsAction;
use yiiunit\TestCase;

/**
 * @group rest
 */
class OptionsActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    public function testCollectionOptions(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        $controller = new \yii\web\Controller('test', Yii::$app);
        $action = new OptionsAction('options', $controller);

        $action->run();

        $headers = Yii::$app->getResponse()->getHeaders();
        $this->assertSame('GET, POST, HEAD, OPTIONS', $headers->get('Allow'));
        $this->assertSame('GET, POST, HEAD, OPTIONS', $headers->get('Access-Control-Allow-Methods'));
    }

    public function testResourceOptions(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        $controller = new \yii\web\Controller('test', Yii::$app);
        $action = new OptionsAction('options', $controller);

        $action->run('123');

        $headers = Yii::$app->getResponse()->getHeaders();
        $this->assertSame('GET, PUT, PATCH, DELETE, HEAD, OPTIONS', $headers->get('Allow'));
        $this->assertSame('GET, PUT, PATCH, DELETE, HEAD, OPTIONS', $headers->get('Access-Control-Allow-Methods'));
    }

    public function testCustomCollectionOptions(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        $controller = new \yii\web\Controller('test', Yii::$app);
        $action = new OptionsAction('options', $controller, [
            'collectionOptions' => ['GET', 'OPTIONS'],
        ]);

        $action->run();

        $headers = Yii::$app->getResponse()->getHeaders();
        $this->assertSame('GET, OPTIONS', $headers->get('Allow'));
    }

    public function testCustomResourceOptions(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';

        $controller = new \yii\web\Controller('test', Yii::$app);
        $action = new OptionsAction('options', $controller, [
            'resourceOptions' => ['GET', 'DELETE'],
        ]);

        $action->run('456');

        $headers = Yii::$app->getResponse()->getHeaders();
        $this->assertSame('GET, DELETE', $headers->get('Allow'));
    }

    public function testNonOptionsRequestReturns405(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new \yii\web\Controller('test', Yii::$app);
        $action = new OptionsAction('options', $controller);

        $action->run();

        $this->assertSame(405, Yii::$app->getResponse()->getStatusCode());
    }
}
