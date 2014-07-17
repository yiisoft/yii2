<?php

return [
    'username' => 'userName',
    'auth_key' => function ($fixture, $faker, $index) {
        $fixture['auth_key'] = Yii::$app->getSecurity()->generateRandomKey();

        return $fixture;
    },
    'password_hash' => function ($fixture, $faker, $index) {
        $fixture['password_hash'] = Yii::$app->getSecurity()->generatePasswordHash('password_' . $index);

        return $fixture;
    },
    'password_reset_token' => function ($fixture, $faker, $index) {
        $fixture['password_reset_token'] = Yii::$app->getSecurity()->generateRandomKey() . '_' . time();

        return $fixture;
    },
    'created_at' => function ($fixture, $faker, $index) {
        $fixture['created_at'] = time();

        return $fixture;
    },
    'updated_at' => function ($fixture, $faker, $index) {
        $fixture['updated_at'] = time();

        return $fixture;
    },
    'email' => 'email',
];
