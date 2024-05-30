<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

/**
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.13
 */
class Variadic
{
    public function __construct(QuxInterface ...$quxes)
    {
    }
}
