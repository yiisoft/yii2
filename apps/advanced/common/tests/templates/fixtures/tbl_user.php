<?php

use yii\helpers\Security;

return [
    'username' => 'userName',
    'auth_key' => function ($fixture, $faker, $index) {
        $fixture['auth_key'] = Security::generateRandomKey();

        return $fixture;
    },
    'password_hash' => function ($fixture, $faker, $index) {
        $fixture['password_hash'] = Security::generatePasswordHash('password_' . $index);

        return $fixture;
    },
    'password_reset_token' => function ($fixture, $faker, $index) {
        $fixture['password_reset_token'] = Security::generateRandomKey() . '_' . time();

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
