<?php

namespace yii\db;

use yii\db\fake\FakeConnection;

/**
 * Class MigrationReverser
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.13
 */
class MigrationReverser
{
    /**
     * @var ReversibleMigrationInterface|Migration
     */
    protected $migration;

    /**
     * MigrationReverser constructor.
     *
     * @param ReversibleMigrationInterface $migration
     */
    function __construct(ReversibleMigrationInterface $migration)
    {
        $this->migration = $migration;
    }

    /**
     * Applies migration using `change()` method without reversing
     */
    public function apply()
    {
        $this->checkCommands($this->collectCommands());
        $this->migration->change();
    }

    /**
     * Apply reverse migration using `change()` method
     */
    public function revert()
    {
        $commands = $this->collectCommands();
        $this->checkCommands($commands);

        $reversedCommands = $this->reverse($commands);

        foreach ($reversedCommands as $command) {
            call_user_func_array([$this->migration, $command[0]], $command[1]);
        }
    }

    protected function collectCommands()
    {
        $connection = $this->migration->db;
        $fakeConnection = new FakeConnection($connection);

        $this->migration->db = $fakeConnection;
        $this->migration->change();
        $commands = $fakeConnection->getLogger()->getExecutedCommands();
        $this->migration->db = $connection;

        return $commands;
    }

    protected function checkCommands($commands)
    {
        foreach ($commands as $command) {
            if (!$this->canReverse($command[0])) {
                throw new IrreversibleCommandException('Command "' . $command[0] . '" can not be reversed automatically. It should be located either in "up" or "down" methods');
            }
        }

        return $commands;
    }

    /**
     * Checks whether $commandName can be revered automatically
     *
     * @param string $commandName
     * @return bool
     */
    public function canReverse($commandName)
    {
        return method_exists($this, 'reverse' . $commandName);
    }

    protected function reverseCreateTable($arguments)
    {
        return ['dropTable', [$arguments[0]]];
    }

    // TODO: other methods that can be reversed

    /**
     * Reverses $commands TODO: enhance docs
     *
     * @param string $commands
     * @return array
     */
    protected function reverse($commands)
    {
        $result = [];
        foreach ($commands as $command) {
            $result[] = call_user_func([$this, 'reverse' . $command[0]], $command[1]);
        }

        return $result;
    }
}
