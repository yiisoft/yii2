<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * The items can either be roles or permissions.
 *
 * @property string $name the name of the item. This must be globally unique.
 * @property integer $type the type of the item. This should be either [[TYPE_ROLE]] or [[TYPE_PERMISSION]].
 * @property string $description
 * @property string $rule_name
 * @property string $data additional data associated with this item
 * @property integer $created_at UNIX timestamp representing the item creation time
 * @property integer $updated_at UNIX timestamp representing the item updating time
 *
 * @property Assignment[] $assignments
 * @property Rule $rule
 * @property Item[] $childrens
 * @property Item[] $parents
 *
 * @author Angel (Faryshta) Guevara <angeldelcaos@gmail.com>
 * @since 2.0.2
 */
class Item extends \yii\rbac\ActiveRecord
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return $this->authManager->itemTable;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [TimestampBehavior::className()];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type'], 'in', 'range' => [self::TYPE_ROLE, self::TYPE_PERMISSION]],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],	
            [['name', 'rule_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignments()
    {
        return $this->hasMany(
                Assignment::className(), ['item_name' => 'name']
            )->inverseOf('item');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRule()
    {
        return $this->hasOne(Rule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildrens()
    {
        return $this->hasMany(self::className(), ['name' => 'child'])
            ->viaTable(ItemChild::tableName(), ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(self::className(), ['name' => 'parent'])
            ->viaTable(ItemChild::tableName(), ['child' => 'name']);
    }
}
