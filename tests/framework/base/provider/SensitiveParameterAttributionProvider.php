<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\provider;

use yii\base\Security;
use yii\web\IdentityInterface;
use yii\web\User;

/**
 * Data provider for {@see \yiiunit\framework\base\SensitiveParameterAttributionTest} test cases.
 *
 * Provides every `(class, method, parameterName)` triple in the framework whose formal parameter must carry the
 * `#[\SensitiveParameter]` attribute so that secret values are redacted from stack traces.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class SensitiveParameterAttributionProvider
{
    /**
     * @return array<int, array{class-string, string, string}>
     */
    public static function attributedParameters(): array
    {
        return [
            [IdentityInterface::class, 'findIdentityByAccessToken', 'token'],
            [Security::class, 'decrypt', 'secret'],
            [Security::class, 'decryptByKey', 'inputKey'],
            [Security::class, 'decryptByPassword', 'password'],
            [Security::class, 'encrypt', 'secret'],
            [Security::class, 'encryptByKey', 'inputKey'],
            [Security::class, 'encryptByPassword', 'password'],
            [Security::class, 'generatePasswordHash', 'password'],
            [Security::class, 'hashData', 'key'],
            [Security::class, 'hkdf', 'inputKey'],
            [Security::class, 'maskToken', 'token'],
            [Security::class, 'pbkdf2', 'password'],
            [Security::class, 'unmaskToken', 'maskedToken'],
            [Security::class, 'validateData', 'key'],
            [Security::class, 'validatePassword', 'password'],
            [User::class, 'loginByAccessToken', 'token'],
        ];
    }
}
