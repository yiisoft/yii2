<?php

namespace yiiunit\framework\base\models;

/**
 * Description of User
 *
 * @property boolean $isNewRecord
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0.8
 */
class User extends \yii\base\Model
{
    public $id;
    public $nik;
    public $name;
    private static $_rows = [
        1 => [1, 'Misbahul Munir'],
        2 => [2, 'Mujib Masyhudi'],
        3 => [3426, 'Orang Itu'],
        4 => [45, 'Hafid Muhlasin'],
        5 => [5758, 'Peter Kambey'],
        6 => [6279, 'Kau Tahu Siapa'],
        7 => [75, 'Henry Dewa'],
        8 => [81, 'Surya Sheillendra'],
        9 => [9, 'Yang Lain'],
    ];
    private $_isNewRecord = true;

    public function rules()
    {
        return[
            [['id', 'name'], 'required'],
            [['nik'], 'safe', 'except' => ['update']]
        ];
    }

    public static function findAll($ids, $indexBy = null)
    {
        $result = [];
        foreach ((array) $ids as $id) {
            list($nik, $name) = self::$_rows[$id];
            $model = new self(['id' => $id, 'nik' => $nik, 'name' => $name]);
            $model->_isNewRecord = false;
            if ($indexBy !== null) {
                $result[$model->$indexBy] = $model;
            } else {
                $result[] = $model;
            }
        }
        return $result;
    }

    public function getIsNewRecord()
    {
        return $this->_isNewRecord;
    }
}
