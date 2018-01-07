<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use Yii;
use yii\base\ExitException;
use yii\widgets\Pjax;
use yiiunit\TestCase;

class PjaxTest extends TestCase
{
    public function testInitWithPjax()
    {
        $this->mockWebApplication();
        $request = Yii::$app->getRequest();
        $request->addHeader('X-Pjax', 1);
        $request->addHeader('X-Pjax-Container', '#p0');
        $request->url = '/test';
        new Pjax();
        $result = '<![CDATA[YII-BLOCK-HEAD]]><![CDATA[YII-BLOCK-BODY-BEGIN]]>';
        /*
        * Unfortunately I have to do this twice to avoid php unit message like
        * 'Test code or tested code did not (only) close its own output buffers'
        */
        ob_end_flush();
        ob_end_flush();
        $this->expectOutputString($result);
    }


    public function testRunWithPjax()
    {
        $this->mockWebApplication();
        $request = Yii::$app->getRequest();
        $request->addHeader('X-Pjax', 1);
        $request->addHeader('X-Pjax-Container', '#p1');
        $request->url = '/test';
        $result = '<![CDATA[YII-BLOCK-HEAD]]><![CDATA[YII-BLOCK-BODY-BEGIN]]>';
        $this->expectOutputString($result);
        $this->expectException(ExitException::class);// But why exception? I want to check output here!
        $pjax = new Pjax();
        $pjax->run();
    }

    public function testNonPjaxRequest()
    {
        $this->mockWebApplication();
        $pjax = new Pjax();
        $result = sprintf(
            '<div id="%s" data-pjax-container="%s" data-pjax-push-state data-pjax-timeout="%d"></div>',
            $pjax->getId(),
            '',
            $pjax->timeout
        );
        $this->expectOutputString($result);
        $pjax->run();
    }
}
