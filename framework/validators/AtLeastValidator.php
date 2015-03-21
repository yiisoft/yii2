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
 * AtLeastValidator checks if at least one of many attributes are filled.
 *
 * In the following example, the `attr1`, `attr2` and `attr3` attributes will
 * be verified. If at least one of them are not filled, all will receive an error:
 *
 * ~~~[php]
 *      // in rules()
 *      return [
 *          ['attr1', 'atLeast', 'with' => ['attr2', 'attr3']],
 *      ];
 * ~~~
 *
 * In the following example, the `attr1`, `attr2` and `attr3` attributes will
 * be verified. If at least two of them are not filled, `attr1` and `attr3` will
 * receive an error:
 *
 * ~~~[php]
 *      // in rules()
 *      return [
 *          ['attr1', 'atLeast', 'with' => ['attr2', 'attr3'], 'min' => 2, 'errorIn' => ['attr1', 'attr3']],
 *      ];
 * ~~~
 *
 * Important: Only one attribute should be specified in first param of the rule.
 * The aditional ones must go into [[with]] param.
 *
 * The attributes that will receive the error message can be specified into [[errorIn]]
 * param. If none is specified, all envolved attributes will receive the error.
 *
 * @author Sidney Lins <slinstj@gmail.com>
 * @since 2.0.4
 */
class AtLeastValidator extends Validator
{
    /**
     * @var array|string the required list of aditional attributes in which to check
     * at least one filled.
     */
    public $with;
    /**
     * @var integer the minimun required quantity of attributes that must to be filled.
     * Defaults to 1.
     */
    public $min = 1;
    /**
     * @var array the list of attributes that should receive the error message.
     * Defaults to all attributes being validated (i.e, attribute + [[with]] attributes).
     */
    public $errorIn;
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
        if (empty($this->with)) {
            throw new InvalidConfigException('Param "with" can not be null');
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'Please, fill {attributesList}.');
        }
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $attributes = array_merge((array) $attribute, (array) $this->with);
        $filled = 0;
        foreach ($attributes as $attribute) {
            $value = $model->$attribute;
            $attributesListLabels[] = '"' . $model->generateAttributeLabel($attribute) . '"';
            $filled += !empty($value) ? 1 : 0;
        }

        if (!$filled) {
            $attributesList = implode(' or ', $attributesListLabels);
            $errorIn = !empty($this->errorIn) ? (array) $this->errorIn : $attributes;
            foreach ($errorIn as $attribute) {
                $this->addError($model, $attribute, $this->message, [
                    'attributesList' => $attributesList,
                ]);
            }
        }
    }
}
