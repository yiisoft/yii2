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
 * This is the model class to handle rules assigned to roles and permissions.
 *
 * @property string $name
 * @property string $data
 * @property integer $created_at UNIX timestamp representing the rule creation time
 * @property integer $updated_at UNIX timestamp representing the rule updating time
 *
 * @property AuthItem[] $items
 *
 * @author Angel (Faryshta) Guevara <angeldelcaos@gmail.com>
 * @since 2.0.2
 */
class AuthRule extends \yii\rbac\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return $this->authManager->ruleTable;
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
            [['name'], 'required'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(AuthItem::className(), ['rule_name' => 'name'])
            ->inverseOf('rule');
    }
}
