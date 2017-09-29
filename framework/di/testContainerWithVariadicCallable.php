<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

$container = new Container();
$func = function(QuxInterface ...$quxes) {
    return "That's a whole lot of quxes!";
};
$container->invoke($func);
