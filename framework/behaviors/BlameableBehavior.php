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
 * BlameableBehavior 用来自动地把指定属性设置为当前的用户 ID。
 *
 * 要使用 BlameableBehavior，把下面的代码加到你的 ActiveRecord 类中：
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
 * 默认情况下，当关联的 AR 对象执行插入操作时，BlameableBehavior 将会给 `created_by` 和 `updated_by`
 * 两个属性赋值为当前的用户 ID。而当 AR 对象执行更新操作时，
 * 它只给 `updated_by` 属性赋值为当前的用户 ID。
 *
 * 由于属性值是被这个行为自动设置，所以属性值不必用户输入也因此没有必要验证。
 * 因此，`created_by` 和 `updated_by` 这两个属性不应该出现在 [[\yii\base\Model::rules()|rules()]] 这个模型方法中。
 *
 * 如果你的属性名不一样，
 * 你可以像下面这样配置 [[createdByAttribute]] 和 [[updatedByAttribute]]。
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
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class BlameableBehavior extends AttributeBehavior
{
    /**
     * @var string 接收当前用户 ID 值的属性。
     * 如果你不想记录当前创建者 ID 就把它设置为 false。
     */
    public $createdByAttribute = 'created_by';
    /**
     * @var string 接收当前用户 ID 值的属性。
     * 如果你不想记录当前更新者 ID 就把它设置为 false。
     */
    public $updatedByAttribute = 'updated_by';
    /**
     * {@inheritdoc}
     *
     * 如果这个属性是 `null`，那么将使用 `Yii::$app->user->id` 的值作为 $value 的值。
     */
    public $value;
    /**
     * @var mixed 当用户是未登录状态下的默认值。
     * @since 2.0.14
     */
    public $defaultValue;


    /**
     * {@inheritdoc}
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
    }

    /**
     * {@inheritdoc}
     *
     * 如果 [[value]] 属性是 `null`，那么将使用 [[defaultValue]] 的值作为 $value 的值。
     */
    protected function getValue($event)
    {
        if ($this->value === null && Yii::$app->has('user')) {
            $userId = Yii::$app->get('user')->id;
            if ($userId === null) {
                return $this->getDefaultValue($event);
            }

            return $userId;
        }

        return parent::getValue($event);
    }

    /**
     * 获得默认值。
     * @param \yii\base\Event $event
     * @return array|mixed
     * @since 2.0.14
     */
    protected function getDefaultValue($event)
    {
        if ($this->defaultValue instanceof \Closure || (is_array($this->defaultValue) && is_callable($this->defaultValue))) {
            return call_user_func($this->defaultValue, $event);
        }

        return $this->defaultValue;
    }
}
