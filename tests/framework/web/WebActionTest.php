<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use PHPUnit\Framework\Attributes\Group;
use yii\web\BadRequestHttpException;
use yiiunit\framework\web\stub\standalone\CoerceIntAction;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\web\Action} HTTP-aware standalone parameter binding.
 *
 * Verifies that standalone HTTP actions get the same scalar coercion, type validation, and missing-parameter handling
 * as controller-bound actions, mirroring {@see \yii\web\Controller::bindActionParams()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('web')]
final class WebActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    public function testWebActionCoercesScalarParam(): void
    {
        $action = new CoerceIntAction('coerce', null);

        $result = $action->runWithParams(['id' => '7']);

        self::assertSame(
            7,
            $result,
            "Scalar string '7' should be coerced to int 7 by HTTP-aware binder.",
        );
    }

    public function testThrowBadRequestHttpExceptionWhenScalarParamFailsTypeCoercion(): void
    {
        $action = new CoerceIntAction('coerce', null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid data received for parameter "id".');

        $action->runWithParams(['id' => 'abc']);
    }

    public function testThrowBadRequestHttpExceptionWhenRequiredParamMissing(): void
    {
        $action = new CoerceIntAction('coerce', null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Missing required parameters: id');

        $action->runWithParams([]);
    }
}
