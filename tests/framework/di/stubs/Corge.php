<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

class Corge extends BaseObject
{
    public $map;

    public function __construct(array $map, $config = [])
    {
        $this->map = $map;
        parent::__construct($config);
    }
}
