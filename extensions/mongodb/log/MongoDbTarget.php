<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb\log;

use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\VarDumper;
use yii\log\Target;
use yii\mongodb\Connection;

/**
 * MongoDbTarget stores log messages in a MongoDB collection.
 *
 * By default, MongoDbTarget stores the log messages in a MongoDB collection named 'log'.
 * The collection can be changed by setting the [[logCollection]] property.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class MongoDbTarget extends Target
{
    /**
     * @var Connection|string the MongoDB connection object or the application component ID of the MongoDB connection.
     * After the MongoDbTarget object is created, if you want to change this property, you should only assign it
     * with a MongoDB connection object.
     */
    public $db = 'mongodb';
    /**
     * @var string|array the name of the MongoDB collection that stores the session data.
     * Please refer to [[Connection::getCollection()]] on how to specify this parameter.
     * This collection is better to be pre-created with fields 'id' and 'expire' indexed.
     */
    public $logCollection = 'log';


    /**
     * Initializes the MongoDbTarget component.
     * This method will initialize the [[db]] property to make sure it refers to a valid MongoDB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * Stores log messages to MongoDB collection.
     */
    public function export()
    {
        $collection = $this->db->getCollection($this->logCollection);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                $text = VarDumper::export($text);
            }
            $collection->insert([
                'level' => $level,
                'category' => $category,
                'log_time' => $timestamp,
                'prefix' => $this->getMessagePrefix($message),
                'message' => $text,
            ]);
        }
    }
}