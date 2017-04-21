<?php
/**
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
