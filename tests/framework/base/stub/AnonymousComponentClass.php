<?php

declare(strict_types=1);

use yii\base\Component;
use yii\base\Behavior;

$obj = new class () extends Component {
    public $foo = 0;
};

$obj->attachBehavior('bar', (new class () extends Behavior {
    public function events()
    {
        return [
            'barEventOnce' => function ($event) {
                $this->owner->foo++;
                $this->detach();
            },
        ];
    }
}));

return $obj;
