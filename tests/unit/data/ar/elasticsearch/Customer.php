<?php
namespace yiiunit\data\ar\elasticsearch;

use yii\elasticsearch\Command;
use yiiunit\extensions\elasticsearch\ActiveRecordTest;

/**
 * Class Customer
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $address
 * @property integer $status
 */
class Customer extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public $status2;

    public static function primaryKey()
    {
        return ['id'];
    }

    public function attributes()
    {
        return ['id', 'name', 'email', 'address', 'status'];
    }

    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->orderBy('created_at');
    }

    public function getExpensiveOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->filter(['range' => ['total' => ['gte' => 50]]])->orderBy('id');
    }

    public function getExpensiveOrdersWithNullFK()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->filter(['range' => ['total' => ['gte' => 50]]])->orderBy('id');
    }

    public function getOrdersWithNullFK()
    {
        return $this->hasMany(OrderWithNullFK::className(), ['customer_id' => 'id'])->orderBy('created_at');
    }

    public function afterSave($insert, $changedAttributes)
    {
        ActiveRecordTest::$afterSaveInsert = $insert;
        ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * sets up the index for this record
     * @param Command $command
     * @param boolean $statusIsBoolean
     */
    public static function setUpMapping($command, $statusIsBoolean = false)
    {
        $command->deleteMapping(static::index(), static::type());
        $command->setMapping(static::index(), static::type(), [
            static::type() => [
                "_id" => ["path" => "id", "index" => "not_analyzed", "store" => "yes"],
                "properties" => [
                    "name" =>        ["type" => "string", "index" => "not_analyzed"],
                    "email" =>       ["type" => "string", "index" => "not_analyzed"],
                    "address" =>     ["type" => "string", "index" => "analyzed"],
                    "status" => $statusIsBoolean ? ["type" => "boolean"] : ["type" => "integer"],
                ]
            ]
        ]);

    }

    /**
     * @inheritdoc
     * @return CustomerQuery
     */
    public static function find()
    {
        return new CustomerQuery(get_called_class());
    }
}
