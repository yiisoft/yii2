<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

use yii\base\Component;

/**
 * Stub {@see Component} with a constructor that accepts extra arguments before the config array.
 */
final class NewComponent2 extends Component
{
    public $a;
    public $b;
    public $c;

    public function __construct($b, $c, $config = [])
    {
        $this->b = $b;
        $this->c = $c;

        parent::__construct($config);
    }
}
