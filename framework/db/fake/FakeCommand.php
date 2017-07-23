<?php

namespace yii\db\proxy;

/**
 * Class FakeCommand logs commands to be executed and prevents real execution
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.12
 */
class FakeCommand extends \yii\db\Command
{
    const EXECUTED_COMMAND_LOG = 'executed_command';

    protected function recordCommand($name, $params)
    {
        $this->db->getLogger()->log([$name, $params], self::EXECUTED_COMMAND_LOG);

        return $this;
    }

    public function createTable($table, $columns, $options = null)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropTable($table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function createIndex($name, $table, $columns, $unique = false)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropIndex($name, $table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropForeignKey($name, $table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function addDefaultValue($name, $table, $column, $value)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function addUnique($name, $table, $columns)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropUnique($name, $table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function addPrimaryKey($name, $table, $columns)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropPrimaryKey($name, $table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropDefaultValue($name, $table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function alterColumn($table, $column, $type)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function delete($table, $condition = '', $params = [])
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function addCheck($name, $table, $expression)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropCheck($name, $table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function addColumn($table, $column, $type)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropColumn($table, $column)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function addCommentOnColumn($table, $column, $comment)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropCommentFromColumn($table, $column)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function addCommentOnTable($table, $comment)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function dropCommentFromTable($table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function renameColumn($table, $oldName, $newName)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function renameTable($table, $newName)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function truncateTable($table)
    {
        return $this->recordCommand(__FUNCTION__, func_get_args());
    }

    public function execute()
    {
        return true;
    }
}
