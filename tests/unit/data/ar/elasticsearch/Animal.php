<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar\elasticsearch;

/**
 * Class Animal
 *
 * @author Jose Lorente <jose.lorente.martin@gmail.com>
 * @since 2.0
 */
class Animal extends ActiveRecord {

    public $does;

    public static function primaryKey() {
        return ['id'];
    }

    public static function type() {
        return 'test_animals';
    }

    public function attributes() {
        return ['id', 'type'];
    }

    /**
     * sets up the index for this record
     * @param Command $command
     */
    public static function setUpMapping($command) {
        $command->deleteMapping(static::index(), static::type());
        $command->setMapping(static::index(), static::type(), [
            static::type() => [
                "_id" => ["path" => "id", "index" => "not_analyzed", "store" => "yes"],
                "properties" => [
                    "type" => ["type" => "string", "index" => "not_analyzed"]
                ]
            ]
        ]);
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
        $class = $row['_source']['type'];
        return new $class;
    }

}
