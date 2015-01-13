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
 * This is the model class for role and permission assignment to users
 *
 * @property string $item_name the item name
 * @property string|integer $user_id user ID (see [[\yii\web\User::id]])
 * @property integer $created_at UNIX timestamp representing the assignment creation time
 *
 * @property AuthItem $item
 *
 * @author Angel (Faryshta) Guevara <angeldelcaos@gmail.com>
 * @since 2.0.2
 */
class AuthAssignment extends \yii\rbac\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return $this->authManager->assignmentTable;
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
            [['item_name', 'user_id'], 'required'],
            [['created_at'], 'integer'],
            [['item_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_name' => 'Item Name',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
    }
}
