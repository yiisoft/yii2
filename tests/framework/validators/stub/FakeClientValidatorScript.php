<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators\stub;

use yii\base\BaseObject;
use yii\base\Model;
use yii\validators\client\ClientValidatorScriptInterface;
use yii\validators\Validator;
use yii\web\View;

/**
 * Implementation of {@see ClientValidatorScriptInterface} used to exercise the `clientScript` materialization and
 * delegation branches in the built-in validators without depending on the `yiisoft/yii2-jquery` extension.
 *
 * The {@see $lastGetClientOptionsCall} and {@see $lastRegisterCall} properties record the arguments passed on each
 * invocation so tests can assert the correct delegation semantics. The {@see $clientOptionsReturnValue} and
 * {@see $registerReturnValue} properties allow tests to inject distinctive return values so the forwarding can be
 * observed end-to-end.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class FakeClientValidatorScript extends BaseObject implements ClientValidatorScriptInterface
{
    /**
     * @var array{0: Validator, 1: Model, 2: string}|null
     */
    public array|null $lastGetClientOptionsCall = null;

    /**
     * @var array{0: Validator, 1: Model, 2: string, 3: View}|null
     */
    public array|null $lastRegisterCall = null;

    /**
     * @var array<string, mixed>
     */
    public array $clientOptionsReturnValue = [];

    public string $registerReturnValue = '';

    public function getClientOptions(Validator $validator, Model $model, string $attribute): array
    {
        $this->lastGetClientOptionsCall = [$validator, $model, $attribute];

        return $this->clientOptionsReturnValue;
    }

    public function register(Validator $validator, Model $model, string $attribute, View $view): string
    {
        $this->lastRegisterCall = [$validator, $model, $attribute, $view];

        return $this->registerReturnValue;
    }
}
