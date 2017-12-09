<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;

/**
 * AtLeastValidator checks if at least $min of many attributes are filled.
 *
 * In the following example, the `attr1`, `attr2` and `attr3` attributes will
 * be validated. Validation will pass if at least one attribute is filled.
 * Otherwise, all the attributes will receive an error.
 *
 * ```php
 *  ['attr1', 'atLeast', 'alternativeAttributes' => ['attr2', 'attr3']],
 * ```
 *
 * In the following example, the `attr1`, `attr2` and `attr3` attributes will
 * be validated. Validation will pass if at least two attributes are filled.
 * Otherwise `attr1` and `attr3` will receive an error:
 *
 * ```php
 * [
 *    'attr1', 'atLeast', 'min' => 2,
 *    'alternativeAttributes' => ['attr2', 'attr3'],
 *    'errorAttributes' => ['attr1', 'attr3']
 * ],
 * ```
 *
 * The attributes that will receive the error message can be specified in [[errorAttributes]]
 * parameter. If none is specified, all the involved attributes will receive the error.
 *
 * @author Sidney Lins <slinstj@gmail.com>
 * @since 2.0.14
 */
class AtLeastValidator extends Validator
{
    /**
     * @var string[] the list of alternative required attributes that will be checked.
     */
    public $alternativeAttributes = [];
    /**
     * @var integer the minimum required quantity of filled attributes to pass the validation.
     * Defaults to 1.
     */
    public $min = 1;
    /**
     * @var array the list of attributes that should receive the error message.
     * Defaults to all attributes being validated (i.e, attribute + [[alternativeAttributes]] attributes).
     */
    public $errorAttributes = [];
    /**
     * @var boolean whether this validation rule should be skipped if the attribute value is null or an empty string.
     */
    public $skipOnEmpty = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->alternativeAttributes)) {
            $this->alternativeAttributes = (array)$this->attributes;
        } else {
            $this->alternativeAttributes = array_merge((array)$this->attributes, $this->alternativeAttributes);
        }

        $this->errorAttributes = empty($this->errorAttributes) ? $this->alternativeAttributes : $this->errorAttributes;

        if ($this->message === null) {
            $this->message = Yii::t('yii', 'At least {min, plural, one{one input} other{# inputs}} of {attributesList} must be filled.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $filledCount = 0;
        $attributesListLabels = [];
        foreach ($this->alternativeAttributes as $attribute) {
            $value = $model->$attribute;
            $attributesListLabels[] = '"' . $model->getAttributeLabel($attribute) . '"';
            $filledCount += $this->isEmpty($value) ? 0 : 1;
        }

        if ($filledCount >= $this->min) {
            return true;
        }

        $orWord = Yii::t('yii', ' or ');
        $attributesList = implode($orWord, $attributesListLabels);
        foreach ($this->errorAttributes as $attribute) {
            $formattedMessage = $this->formatMessage($this->message, [
                'attributesList' => $attributesList,
                'min' => $this->min
            ]);
            if (!in_array($formattedMessage, $model->getErrors($attribute))) {
                $this->addError($model, $attribute, $formattedMessage);
            }
        }
    }
}
