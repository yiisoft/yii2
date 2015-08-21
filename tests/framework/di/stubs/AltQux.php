<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

use yii\base\Object;


/**
 * @author Aleksei Akireikin <opexus@gmail.com>
 * @since 2.0
 */
class AltQux extends Object implements QuxInterface
{
    public $b;

    public function __construct($b = 2, $config = [])
    {
        $this->b = $b;
        parent::__construct($config);
    }

    public function quxMethod()
    {
    }
}
