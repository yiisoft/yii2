<?php
/**
 * TestCase class.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

require_once('PHPUnit/Runner/Version.php');
spl_autoload_unregister(array('Yii', 'autoload'));
require_once('PHPUnit/Autoload.php');
spl_autoload_register(array('Yii', 'autoload')); // put yii's autoloader at the end

/**
 * TestCase is the base class for all test case classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
}
