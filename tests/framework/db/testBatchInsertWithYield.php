<?php
/**
 * Created by PhpStorm.
 * User: lav45
 * Date: 22.01.17
 * Time: 2:56
 *
 * @var $this \yiiunit\framework\db\CommandTest
 */

$rows = call_user_func(function () {
    if (false) {
        yield [];
    }
});

$command = $this->getConnection()->createCommand();
$command->batchInsert(
    '{{customer}}',
    ['email', 'name', 'address'],
    $rows
);
$this->assertEquals(0, $command->execute());