<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\helpers\Json;

/**
 * This validator checks if the attribute value is a valid JSON string.
 *
 * @since 2.0.14
 */
class JsonValidator extends Validator
{
    /**
     * @var string user-defined error message which is used when the validation fails.
     *
     * You may use the following placeholders in the message:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $message;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} must be a valid JSON string.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $result = null;

        $isValid = Json::validate($value);
        if ($isValid === false) {
            $result = [$this->message, []];
        }

        return $result;
    }
}
