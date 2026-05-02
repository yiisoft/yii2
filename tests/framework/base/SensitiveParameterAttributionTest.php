<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use ReflectionMethod;
use SensitiveParameter;
use SensitiveParameterValue;
use Throwable;
use ValueError;
use yii\base\InvalidArgumentException;
use yii\base\Security;
use yiiunit\framework\base\provider\SensitiveParameterAttributionProvider;
use yiiunit\TestCase;

/**
 * Unit tests for the `#[\SensitiveParameter]` attribution contract on secret-bearing parameters in
 * {@see \yii\base\Security}, {@see \yii\web\User}, and {@see \yii\web\IdentityInterface}.
 *
 * Verifies the static contract via reflection and the runtime redaction performed by the PHP engine on
 * {@see \Throwable::getTrace()} and {@see \Throwable::getTraceAsString()}.
 *
 * {@see SensitiveParameterAttributionProvider} for test case data providers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('base')]
final class SensitiveParameterAttributionTest extends TestCase
{
    #[DataProviderExternal(SensitiveParameterAttributionProvider::class, 'attributedParameters')]
    public function testParameterCarriesSensitiveAttribute(string $class, string $method, string $parameterName): void
    {
        $reflection = new ReflectionMethod($class, $method);

        $matched = null;

        foreach ($reflection->getParameters() as $parameter) {
            if ($parameter->getName() === $parameterName) {
                $matched = $parameter;
                break;
            }
        }

        self::assertNotNull(
            $matched,
            "Parameter \${$parameterName} must exist on {$class}::{$method}().",
        );
        self::assertNotEmpty(
            $matched->getAttributes(SensitiveParameter::class),
            "Parameter \${$parameterName} must declare `#[\\SensitiveParameter]`.",
        );
    }

    public function testValidatePasswordRedactsPasswordInExceptionTrace(): void
    {
        $sentinel = 'SENTINEL_PASSWORD_47A1';

        $security = new Security();

        try {
            $security->validatePassword($sentinel, 'not-a-real-bcrypt-hash');

            self::fail("Invalid hash must trigger an 'InvalidArgumentException'.");
        } catch (InvalidArgumentException $exception) {
            $this->assertSentinelRedacted(
                $exception,
                Security::class,
                'validatePassword',
                $sentinel,
            );
        }
    }

    public function testPbkdf2RedactsPasswordInExceptionTrace(): void
    {
        $sentinel = 'SENTINEL_PBKDF2_9F22';

        $security = new Security();

        try {
            $security->pbkdf2('not-a-real-algo', $sentinel, 'salt', 1, 0);

            self::fail("Invalid algorithm must trigger a 'ValueError'.");
        } catch (ValueError $exception) {
            $this->assertSentinelRedacted(
                $exception,
                Security::class,
                'pbkdf2',
                $sentinel,
            );
        }
    }

    private function assertSentinelRedacted(
        Throwable $exception,
        string $class,
        string $method,
        string $sentinel,
    ): void {
        if ((bool) ini_get('zend.exception_ignore_args')) {
            self::markTestSkipped(
                "'zend.exception_ignore_args' must be disabled ('0') for redaction assertions to work.",
            );
        }

        self::assertStringNotContainsString(
            $sentinel,
            $exception->getTraceAsString(),
            'Sentinel must not appear in the trace string.',
        );

        foreach ($exception->getTrace() as $frame) {
            foreach ($frame['args'] ?? [] as $argument) {
                self::assertNotSame(
                    $sentinel,
                    $argument,
                    'Sentinel must not appear as a raw trace argument.',
                );
            }
        }

        $wrapped = false;

        foreach ($exception->getTrace() as $frame) {
            if (($frame['class'] ?? null) !== $class || ($frame['function'] ?? null) !== $method) {
                continue;
            }

            foreach ($frame['args'] ?? [] as $argument) {
                if ($argument instanceof SensitiveParameterValue) {
                    $wrapped = true;
                    break 2;
                }
            }
        }

        self::assertTrue(
            $wrapped,
            "Sensitive arg must be wrapped in `SensitiveParameterValue` for {$class}::{$method}().",
        );
    }
}
