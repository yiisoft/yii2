<?php

namespace yii\db;

use Yii;
use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * QuerRecord is the base class for classes representing query statement data in terms of objects.
 *
 * To declare an QueryRecord class you need to extend [[\yii\db\QueryRecord]] and
 * implement the `query` method:
 *
 * ```php
 * <?php
 *
 * class InvoicePaid extends \yii\db\QueryRecord
 * {
 *     public function attributes()
 *     {
 *         return ['invoice_id', 'total'];
 *     }
 *
 *     public static function query()
 *     {
 *         return (new Query())
 *             ->select(['invoice_id', 'total' => 'sum(value)'])
 *             ->from('payment')
 *             ->groupBy(['invoice_id']);
 *     }
 * }
 * ```
 *
 * The `query` method only has to return the [[\yii\db\Query]] representation of the class.
 *
 * Below is an example showing some typical usage of QueryRecord:
 *
 * ```php
 * $paids = InvoicePaid::find()
 *              ->where(['>', 'total', 3426])
 *              ->all();
 * ```
 *
 * Use as relation
 * ```php
 * class Invoive extends ActiveRecord
 * {
 *     ...
 *     public function getPaid()
 *     {
 *         return $this->hasOne(InvoicePaid::className(), ['invoice_id' => 'id']);
 *     }
 * }
 * ```
 *
 * Then access it as
 * ```php
 * $invoices = Invoice::find()
 *                 ->alias('i')
 *                 ->joinWith(['paid p'])
 *                 ->where('[[i.value]] > [[p.total]]')
 *                 ->all();
 *
 * foreach (Invoice::find()->with('paid')->all() as $model) {
 *     echo $model->paid->total;
 * }
 * ```
 * For more details and usage information on ActiveRecord, see the [guide article on ActiveRecord](guide:db-active-record).
 *
 * @method ActiveQuery hasMany($class, array $link) see [[BaseActiveRecord::hasMany()]] for more info
 * @method ActiveQuery hasOne($class, array $link) see [[BaseActiveRecord::hasOne()]] for more info
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.10
 */
class QueryRecord extends BaseActiveRecord
{

    /**
     * @return Query defition of AR
     */
    public static function query()
    {
        throw new InvalidConfigException(__METHOD__ . '');
    }

    /**
     * @inheritdoc
     */
    public function insert($runValidation = true, $attributes = null)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()])
                ->from([Inflector::camel2id(StringHelper::basename(get_called_class()), '_') => static::query()]);
    }

    /**
     * Returns the database connection used by this AR class.
     * By default, the "db" application component is used as the database connection.
     * You may override this method if you want to use a different database connection.
     * @return Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->getDb();
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {

    }
}
