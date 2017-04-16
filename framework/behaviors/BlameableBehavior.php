<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Yii;
use yii\db\BaseActiveRecord;

/**
 * BlameableBehavior automatically fills the specified attributes with the current user ID.
 *
 * To use BlameableBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * use yii\behaviors\BlameableBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         BlameableBehavior::className(),
 *     ];
 * }
 * ```
 *
 * By default, BlameableBehavior will fill the `created_by` and `updated_by` attributes with the current user ID
 * when the associated AR object is being inserted; it will fill the `updated_by` attribute
 * with the current user ID when the AR object is being updated.
 *
 * Because attribute values will be set automatically by this behavior, they are usually not user input and should therefore
 * not be validated, i.e. `created_by` and `updated_by` should not appear in the [[\yii\base\Model::rules()|rules()]] method of the model.
 *
 * If your attribute names are different, you may configure the [[createdByAttribute]] and [[updatedByAttribute]]
 * properties like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => BlameableBehavior::className(),
 *             'createdByAttribute' => 'author_id',
 *             'updatedByAttribute' => 'updater_id',
 *         ],
 *     ];
 * }
 * ```
 *
 * When retrieving the model, the related user model is available from [[$model->createdByUser]] and
 * [[$model->UpdatedByUser]]
 *
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @author Arno Slatius <a.slatius@gmail.com>
 * @since 2.0
 */
class BlameableBehavior extends AttributeBehavior
{
    /**
     * @var string the attribute that will receive current user ID value
     * Set this property to false if you do not want to record the creator ID.
     */
    public $createdByAttribute = 'created_by';
    /**
     * @var string the attribute that will receive current user ID value
     * Set this property to false if you do not want to record the updater ID.
     */
    public $updatedByAttribute = 'updated_by';
    /**
     * @inheritdoc
     *
     * In case, when the property is `null`, the value of `Yii::$app->user->id` will be used as the value.
     */
    public $value;
    /**
    * @var string the user class to use for the IDs.
    * Defaults to user application components class
    */
    public $userClass;
    /**
    * @var string the ID attribute to use in the relation to the user model.
    * Defaults to 'id'
    */
    public $userIdColumn = 'id';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdByAttribute, $this->updatedByAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedByAttribute,
            ];
        }
        if (empty($this->userClass)) {
            $this->userClass = Yii::$app->get('user', false)->identityClass;
        }
    }

    /**
     * @inheritdoc
     *
     * In case, when the [[value]] property is `null`, the value of `Yii::$app->user->id` will be used as the value.
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            $user = Yii::$app->get('user', false);
            return $user && !$user->isGuest ? $user->id : null;
        }

        return parent::getValue($event);
    }

    /**
     * @return \yii\db\ActiveQuery relation for the creator user model
     */
    public function getCreatedByUser()
    {
        return $this->owner->hasOne($this->userClass, [$this->userIdColumn => $this->createdByAttribute]);
    }

    /**
     * @return \yii\db\ActiveQuery relation for the updater user model
     */
    public function getUpdatedByUser()
    {
        return  $this->owner->hasOne($this->userClass, [$this->userIdColumn => $this->updatedByAttribute]);
    }
}
