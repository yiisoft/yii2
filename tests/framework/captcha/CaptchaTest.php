<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\captcha;

use PHPUnit\Framework\Attributes\Group;
use yii\base\BaseObject;
use yii\base\DynamicModel;
use yii\captcha\Captcha;
use yiiunit\framework\widgets\ClientScriptDispatchTestTrait;
use yiiunit\TestCase;

/**
 * Unit tests for {@see Captcha}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('captcha')]
final class CaptchaTest extends TestCase
{
    use ClientScriptDispatchTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    protected function createWidgetInstance(array $config = []): BaseObject
    {
        return new Captcha(
            array_merge(
                [
                    'model' => new DynamicModel(['captcha' => '']),
                    'attribute' => 'captcha',
                ],
                $config,
            ),
        );
    }

    protected function triggerClientScriptDispatch(BaseObject $widget): void
    {
        /** @var Captcha $widget */
        $widget->registerClientScript();
    }
}
