<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar\mongodb;

/**
 * Class Animal
 *
 * @author Jose Lorente <jose.lorente.martin@gmail.com>
 * @since 2.0
 */
class Animal extends ActiveRecord {

    public $does;

    public static function collectionName() {
        return 'test_animals';
    }

    public function attributes() {
        return ['_id', 'type'];
    }

    public function init() {
        parent::init();
        $this->type = get_called_class();
    }

    public function getDoes() {
        return $this->does;
    }

    /**
     * 
     * @param type $row
     * @return \yiiunit\data\ar\elasticsearch\Animal
     */
    public static function instantiate($row) {
        $class = $row['type'];
        return new $class;
    }

}
