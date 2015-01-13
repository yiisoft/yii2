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
 * This parent/children structure for items.
 *
 * @property string $parent
 * @property string $child
 *
 * @property Item $parentItem
 * @property Item $childItem
 *
 * @author Angel (Faryshta) Guevara <angeldelcaos@gmail.com>
 * @since 2.0.2
 */
class ItemChild extends \yii\rbac\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return $this->authManager->itemChildTable;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent' => 'Parent',
            'child' => 'Child',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentItem()
    {
        return $this->hasOne(Item::className(), ['name' => 'parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildItem()
    {
        return $this->hasOne(Item::className(), ['name' => 'child']);
    }
}
