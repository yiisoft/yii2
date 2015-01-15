<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\rbac\DbManager;
use yii\base\InvalidConfigException;

/**
 * Base class for the ActiveRecord used in the rback module
 *
 * @author Angel (Faryshta) Guevara <angeldelcaos@gmail.com>
 * @since 2.0.2
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord
{
    public function __set($name, $value)
    {
         try {
             parent::__set($name, $value);
         } catch(\Exception $e) {
             $name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
             $this->$name = $value;
         }
    }

    public function __get($name)
    {
         try {
             return parent::__get($name);
         } catch(\Exception $e) {
             $name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
             return $this->$name;
         }
    }
}
