<?php
/**
 * @var $faker \Faker\Generator
 * @var $index integer
 */

$security = Yii::$app->getSecurity();

return [
    'address' => $faker->address,
    'phone' => $faker->phoneNumber,
    'first_name' => $faker->firstName,
];
