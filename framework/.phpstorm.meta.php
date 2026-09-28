<?php
// .phpstorm.meta.php

namespace PHPSTORM_META {

    override(
        \yii\di\Container::get(0),
        map([
            '' => '@',
        ])
    );

    override(
        \yii\di\Instance::ensure(0),
        map([
            '' => '@',
        ])
    );

    override(
        \Yii::createObject(0),
        map([
            '' => '@',
        ])
    );
}
