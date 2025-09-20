<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\validators\client;

use yii\base\Model;
use yii\validators\Validator;
use yii\web\View;

/**
 * ClientValidatorScriptInterface defines the contract for client-side validator script generation.
 *
 * Classes implementing this interface provide methods for retrieving client-side validation options and registering the
 * corresponding JavaScript code for a given validator, model, and attribute.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
interface ClientValidatorScriptInterface
{
    /**
     * Returns client-side validation options for the specified validator, model, and attribute.
     *
     * @param Validator $validator the validator instance.
     * @param Model $model the data model being validated.
     * @param string $attribute the attribute name being validated.
     *
     * @return array client-side validation options.
     *
     * @phpstan-return array<string, mixed>
     * @psalm-return array<string, mixed>
     */
    public function getClientOptions(Validator $validator, Model $model, string $attribute): array;

    /**
     * Registers the client-side validation script for the specified validator, model, and attribute in the view.
     *
     * @param Validator $validator the validator instance.
     * @param Model $model the data model being validated.
     * @param string $attribute the attribute name being validated.
     * @param View $view the view instance where the script will be registered.
     *
     * @return string the JavaScript code for client-side validation.
     */
    public function register(Validator $validator, Model $model, string $attribute, View $view): string;
}
