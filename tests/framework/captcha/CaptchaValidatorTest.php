<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\captcha;

use PHPUnit\Framework\Attributes\Group;
use yii\captcha\CaptchaValidator;
use yii\validators\Validator;
use yiiunit\framework\validators\ClientScriptDispatchTestTrait;
use yiiunit\TestCase;

/**
 * Unit tests for the {@see CaptchaValidator} `clientScript` strategy dispatch.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('captcha')]
#[Group('validators')]
final class CaptchaValidatorTest extends TestCase
{
    use ClientScriptDispatchTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    protected function createValidatorInstance(array $config = []): Validator
    {
        return new CaptchaValidator($config);
    }
}
