<?php

namespace yii\db;

/**
 * Interface ReversibleMigrationInterface
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.13
 */
interface ReversibleMigrationInterface
{
    public function change();
}
