<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\jquery\validators\TrimValidatorJqueryClientScript;
use yii\validators\client\ClientValidatorScriptInterface;

/**
 * This class converts the attribute value(s) to string(s) and strip characters.
 *
 * @since 2.0.46
 */
class TrimValidator extends Validator
{
    /**
     * @var string The list of characters to strip, with `..` can specify a range of characters.
     * For example, set '\/ ' to normalize path or namespace.
     */
    public $chars;
    /**
     * @var bool Whether the filter should be skipped if an array input is given.
     * If true and an array input is given, the filter will not be applied.
     */
    public $skipOnArray = false;
    /**
     * @inheritDoc
     */
    public $skipOnEmpty = false;
    /**
     * Client script class to use for client-side validation.
     */
    public array|ClientValidatorScriptInterface|null $clientScript = null;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (Yii::$app->useJquery && !$this->clientScript instanceof ClientValidatorScriptInterface) {
            $this->clientScript ??= ['class' => TrimValidatorJqueryClientScript::class];
            $this->clientScript = Yii::createObject($this->clientScript);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!$this->skipOnArray || !is_array($value)) {
            $model->$attribute = is_array($value)
                ? array_map([$this, 'trimValue'], $value)
                : $this->trimValue($value);
        }
    }

    /**
     * Converts given value to string and strips declared characters.
     *
     * @param mixed $value the value to strip
     * @return string
     */
    protected function trimValue($value)
    {
        return $this->isEmpty($value) ? '' : trim((string) $value, $this->chars ?: " \n\r\t\v\x00");
    }

    /**
     * @inheritDoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->clientScript instanceof ClientValidatorScriptInterface) {
            return $this->clientScript->register($this, $model, $attribute, $view);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getClientOptions($model, $attribute)
    {
        if ($this->clientScript instanceof ClientValidatorScriptInterface) {
            return $this->clientScript->getClientOptions($this, $model, $attribute);
        }

        return [];
    }
}
