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
 * be verified. If at least one of them are not filled, all will receive an error:
 *
 * ```php
 *  ['attr1', 'atLeast', 'alternativeAttributes' => ['attr2', 'attr3']],
 * ```
 *
 * In the following example, the `attr1`, `attr2` and `attr3` attributes will
 * be verified. If at least two of them are not filled, `attr1` and `attr3` will
 * receive an error:
 *
 * ```php
 *  ['attr1', 'atLeast', 'alternativeAttributes' => ['attr2', 'attr3'], 'min' => 2, 'errorAttributes' => ['attr1', 'attr3']],
 * ```
 *
 * The attributes that will receive the error message can be specified into [[errorAttributes]]
 * param. If none is specified, all envolved attributes will receive the error.
 *
 * @author Sidney Lins <slinstj@gmail.com>
 * @since 2.0.14
 */
class AtLeastValidator extends Validator
{
    /**
     * @var array|string the required list of additional attributes in which to check
     * at least min filled.
     */
    public $alternativeAttributes;
    /**
     * @var integer the minimun required quantity of attributes that must to be filled.
     * Defaults to 1.
     */
    public $min = 1;
    /**
     * @var array the list of attributes that should receive the error message.
     * Defaults to all attributes being validated (i.e, attribute + [[alternativeAttributes]] attributes).
     */
    public $errorAttributes;
    /**
     * @var boolean whether this validation rule should be skipped if the attribute value
     * is null or an empty string.
     */
    public $skipOnEmpty = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->isEmpty($this->alternativeAttributes)) {
            $this->alternativeAttributes = (array)$this->attributes;
        } else {
            $this->alternativeAttributes = array_merge((array)$this->attributes, (array)$this->alternativeAttributes);
        }
        $this->errorAttributes = $this->isEmpty($this->errorAttributes) ? (array)$this->alternativeAttributes : (array)$this->errorAttributes;
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'Please fill at least {min} of {attributesList}.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $filled = 0;
        $attributesListLabels = [];
        foreach ($this->alternativeAttributes as $attribute) {
            $value = $model->$attribute;
            $attributesListLabels[] = '"' . $model->getAttributeLabel($attribute) . '"';
            $filled += !$this->isEmpty($value) ? 1 : 0;
        }

        if ($filled >= $this->min) {
            return true;
        }

        $attributesList = implode(' or ', $attributesListLabels);
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
