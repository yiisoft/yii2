<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Yii;
use yii\base\Event;
use yii\db\BaseActiveRecord;

/**
 * BlameableBehavior automatically fills the specified attributes with the current user ID.
 *
 * To use BlameableBehavior, simply insert the following code to your ActiveRecord class:
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
 * with the current user ID when the AR object is being updated. If your attribute names are different, you may configure
 * the [[attributes]] property like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => BlameableBehavior::className(),
 *             'attributes' => [
 *                 ActiveRecord::EVENT_BEFORE_INSERT => 'author_id',
 *                 ActiveRecord::EVENT_BEFORE_UPDATE => 'updater_id',
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BlameableBehavior extends AttributeBehavior
{
    /**
     * @var array list of attributes that are to be automatically filled with the current user ID.
     * The array keys are the ActiveRecord events upon which the attributes are to be filled with the user ID,
     * and the array values are the corresponding attribute(s) to be updated. You can use a string to represent
     * a single attribute, or an array to represent a list of attributes.
     * The default setting is to update both of the `created_by` and `updated_by` attributes upon AR insertion,
     * and update the `updated_by` attribute upon AR updating.
     */
    public $attributes = [
        BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_by', 'updated_by'],
        BaseActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
    ];
    /**
     * @var callable the value that will be assigned to the attributes. This should be a valid
     * PHP callable whose return value will be assigned to the current attribute(s).
     * The signature of the callable should be:
     *
     * ```php
     * function ($event) {
     *     // return value will be assigned to the attribute(s)
     * }
     * ```
     *
     * If this property is not set, the value of `Yii::$app->user->id` will be assigned to the attribute(s).
     */
    public $value;

    /**
     * Evaluates the value of the user.
     * The return result of this method will be assigned to the current attribute(s).
     * @param Event $event
     * @return mixed the value of the user.
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            $user = Yii::$app->getUser();

            return $user && !$user->isGuest ? $user->id : null;
        } else {
            return call_user_func($this->value, $event);
        }
    }
}
