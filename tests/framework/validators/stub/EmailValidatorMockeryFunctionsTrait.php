<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators\stub;

/**
 * Trait that provides mockery functions for the {@see \yii\validators\EmailValidator} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 *
 * @since 22.0
 */
trait EmailValidatorMockeryFunctionsTrait
{
    private static bool|null $idnToAsciiExists = null;
    private static bool $dnsThrowsException = false;

    protected function stubIdnToAsciiExists(bool $exists): void
    {
        self::$idnToAsciiExists = $exists;
    }

    protected function stubDnsGetRecordThrowsException(bool $throws = true): void
    {
        self::$dnsThrowsException = $throws;
    }

    protected function resetStubs(): void
    {
        self::$idnToAsciiExists = null;
        self::$dnsThrowsException = false;
    }

    public static function getIdnToAsciiExistsStub(): ?bool
    {
        return self::$idnToAsciiExists;
    }

    public static function shouldDnsThrowExceptionStub(): bool
    {
        return self::$dnsThrowsException;
    }
}
