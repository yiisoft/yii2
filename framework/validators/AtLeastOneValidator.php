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
 * AtLeastOneValidator checks if at least one of many attributes are filled.
 *
 * In the following example, the `attr1`, `attr2` and `attr3` attributes will
 * be verified. If none of them are filled, `attr2` and `attr3` will receive an
 * error:
 *
 * ~~~[php]
 *      // in rules()
 *      return [
 *          ['attr1', 'atLeastOne', 'validateWith' => ['attr2', 'attr3'], 'errorAttributes' => ['attr2', 'attr3']],
 *      ];
 * ~~~
 *
 * Important: Only one field must be specified in first param of the rule. The aditional
 * fields must go into [[validateWith]].
 *
 * The attributes that will receive the error message can be specified into [[errorAttributes]]
 * param. If none is specified, all envolved attributes will receive the error.
 *
 * @author Sidney Lins <slinstj@gmail.com>
 * @since 2.0.4
 */
class AtLeastOneValidator extends Validator
{
    /**
     * @var array|string the required list of aditional attributes in which to check
     * at least one filled.
     */
    public $validateWith;
    /**
     * @var array the list of attributes that should receive the error message.
     * Defaults to all attributes being validated.
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
        if (empty($this->validateWith)) {
            throw new InvalidConfigException('Param "validateWith" can not be null');
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'Please, fill {attributesList}.');
        }
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $attributes = array_merge((array) $attribute, (array) $this->validateWith);
        $atLeastOne = false;
        foreach ($attributes as $attribute) {
            $value = $model->$attribute;
            $attributesListLabels[] = '"' . $model->generateAttributeLabel($attribute) . '"';
            $atLeastOne = !empty($value) || $atLeastOne;
        }

        if (!$atLeastOne) {
            $attributesList = implode(' or ', $attributesListLabels);
            $errorAttributes = !empty($this->errorAttributes) ? (array) $this->errorAttributes : $attributes;
            foreach ($errorAttributes as $attribute) {
                $this->addError($model, $attribute, $this->message, [
                    'attributesList' => $attributesList,
                ]);
            }
        }
    }
}
