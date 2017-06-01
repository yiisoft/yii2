<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;
use Yii;
use yii\base\Model;
use yii\db\BaseActiveRecord;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * ImmutableValidator verifies if an attribute is modified.
 *
 * You can use ImmutableValidator to validate the attributes should not be modified when updating model.
 *
 * This validator only applies to ActiveRecord models.
 *
 * This validator does not work when creating new models.
 *
 * Note than this validator does not support `validateValue` method.
 *
 * @author kismilan <qihjun@gmail.com>
 */
class ImmutableValidator extends Validator
{

    /**
     * @var bool this property is overwritten to be false so that this validator will
     * be applied when the value being validated is empty.
     */
    public $skipOnEmpty = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} cannot be modified.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        if($model instanceof BaseActiveRecord && !$model->isNewRecord){
            $changedAttributes = $model->getDirtyAttributes();
            if(array_key_exists($attribute,$changedAttributes)){
                $this->addError($model,$attribute,$this->message);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model,$attribute);

        return 'yii.validation.compare(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * @inheritdoc
     */
    public function getClientOptions($model, $attribute)
    {
        $options = [
            'compareValue' => $model->$attribute,
            'compareAttribute' => Html::getInputId($model, $attribute),
            'message' => $this->message
        ];

        $options['message'] = $this->formatMessage($options['message'], [
            'attribute' => $model->getAttributeLabel($attribute),
        ]);

        return $options;
    }
}
