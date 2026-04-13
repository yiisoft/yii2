<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets\stub;

use yii\base\BaseObject;
use yii\web\client\ClientScriptInterface;
use yii\web\View;

/**
 * Spy implementation of {@see ClientScriptInterface} used to exercise the `clientScript` materialization and
 * dispatch branches in widgets and grid columns without depending on the `yiisoft/yii2-jquery` extension.
 *
 * The {@see $lastGetClientOptionsCall} and {@see $lastRegisterCall} properties record the arguments passed on each
 * invocation so tests can assert the correct delegation semantics. The {@see $clientOptionsReturnValue} property
 * allows tests to inject a distinctive return value so the forwarding can be observed end-to-end.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class FakeClientScript extends BaseObject implements ClientScriptInterface
{
    /**
     * @var array{0: BaseObject, 1: array<string, mixed>}|null
     */
    public ?array $lastGetClientOptionsCall = null;

    /**
     * @var array{0: BaseObject, 1: View, 2: array<string, mixed>}|null
     */
    public ?array $lastRegisterCall = null;

    /**
     * @var array<string, mixed>
     */
    public array $clientOptionsReturnValue = [];

    public function getClientOptions(BaseObject $object, array $options = []): array
    {
        $this->lastGetClientOptionsCall = [$object, $options];

        return $this->clientOptionsReturnValue;
    }

    public function register(BaseObject $object, View $view, array $options = []): void
    {
        $this->lastRegisterCall = [$object, $view, $options];
    }
}
