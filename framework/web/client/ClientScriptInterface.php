<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web\client;

use yii\base\BaseObject;
use yii\web\View;

/**
 * Defines the contract for registering client-side scripts for widgets and components.
 *
 * Classes implementing this interface are responsible for providing client options and registering scripts in the
 * context of Yii2 widgets and views.
 *
 * @template T of BaseObject
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
interface ClientScriptInterface
{
    /**
     * Returns client-side options for the specified component.
     *
     * This method is used to retrieve the options that will be passed to the client-side script.
     *
     * @param T $object the object for which to get the client options.
     * @param array<string, mixed> $options additional options that may influence the client options returned.
     *
     * @return array<string, mixed> the client options for the widget or component.
     */
    public function getClientOptions(BaseObject $object, array $options = []): array;

    /**
     * Registers the client script for the specified widget and view.
     *
     * This method is used to register the necessary client-side scripts for the widget in the given view.
     *
     * @param T $object the object whose client script is to be registered.
     * @param View $view the view in which the client script should be registered.
     * @param array<string, mixed> $options additional options that may influence the script registration process.
     */
    public function register(BaseObject $object, View $view, array $options = []): void;
}
