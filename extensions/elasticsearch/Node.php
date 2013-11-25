<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;


use yii\base\Object;

/**
 * Represents an elastic search cluster node.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Node extends Object
{
	public $host;
	public $port;
}