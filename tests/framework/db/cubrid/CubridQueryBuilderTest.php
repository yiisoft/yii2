<?php

namespace yiiunit\framework\db\cubrid;

use yii\db\Schema;
use yiiunit\framework\db\QueryBuilderTest;

/**
 * @group db
 * @group cubrid
 */
class CubridQueryBuilderTest extends QueryBuilderTest
{
    public $driverName = 'cubrid';

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return [
            [Schema::TYPE_PK, $this->primaryKey(), 'int NOT NULL AUTO_INCREMENT PRIMARY KEY'],
            [Schema::TYPE_PK . '(8)', $this->primaryKey(8), 'int NOT NULL AUTO_INCREMENT PRIMARY KEY'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', $this->primaryKey()->check('value > 5'), 'int NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', $this->primaryKey(8)->check('value > 5'), 'int NOT NULL AUTO_INCREMENT PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_STRING, $this->string(), 'varchar(255)'],
            [Schema::TYPE_STRING . '(32)', $this->string(32), 'varchar(32)'],
            [Schema::TYPE_STRING . ' CHECK (value LIKE "test%")', $this->string()->check('value LIKE "test%"'), 'varchar(255) CHECK (value LIKE "test%")'],
            [Schema::TYPE_STRING . '(32) CHECK (value LIKE "test%")', $this->string(32)->check('value LIKE "test%"'), 'varchar(32) CHECK (value LIKE "test%")'],
            [Schema::TYPE_STRING . ' NOT NULL', $this->string()->notNull(), 'varchar(255) NOT NULL'],
            [Schema::TYPE_TEXT, $this->text(), 'varchar'],
            [Schema::TYPE_TEXT . '(255)', $this->text(), 'varchar', Schema::TYPE_TEXT],
            [Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")', $this->text()->check('value LIKE "test%"'), 'varchar CHECK (value LIKE "test%")'],
            [Schema::TYPE_TEXT . '(255) CHECK (value LIKE "test%")', $this->text()->check('value LIKE "test%"'), 'varchar CHECK (value LIKE "test%")', Schema::TYPE_TEXT . ' CHECK (value LIKE "test%")'],
            [Schema::TYPE_TEXT . ' NOT NULL', $this->text()->notNull(), 'varchar NOT NULL'],
            [Schema::TYPE_TEXT . '(255) NOT NULL', $this->text()->notNull(), 'varchar NOT NULL', Schema::TYPE_TEXT . ' NOT NULL'],
            [Schema::TYPE_SMALLINT, $this->smallInteger(), 'smallint'],
            [Schema::TYPE_SMALLINT . '(8)', $this->smallInteger(8), 'smallint'],
            [Schema::TYPE_INTEGER, $this->integer(), 'int'],
            [Schema::TYPE_INTEGER . '(8)', $this->integer(8), 'int'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', $this->integer()->check('value > 5'), 'int CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', $this->integer(8)->check('value > 5'), 'int CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', $this->integer()->notNull(), 'int NOT NULL'],
            [Schema::TYPE_BIGINT, $this->bigInteger(), 'bigint'],
            [Schema::TYPE_BIGINT . '(8)', $this->bigInteger(8), 'bigint'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', $this->bigInteger()->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', $this->bigInteger(8)->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . ' NOT NULL', $this->bigInteger()->notNull(), 'bigint NOT NULL'],
            [Schema::TYPE_FLOAT, $this->float(), 'float(7)'],
            [Schema::TYPE_FLOAT . '(16)', $this->float(16), 'float(16)'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', $this->float()->check('value > 5.6'), 'float(7) CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16) CHECK (value > 5.6)', $this->float(16)->check('value > 5.6'), 'float(16) CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', $this->float()->notNull(), 'float(7) NOT NULL'],
            [Schema::TYPE_DOUBLE, $this->double(), 'double(15)'],
            [Schema::TYPE_DOUBLE . '(16)', $this->double(16), 'double(16)'],
            [Schema::TYPE_DOUBLE . ' CHECK (value > 5.6)', $this->double()->check('value > 5.6'), 'double(15) CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . '(16) CHECK (value > 5.6)', $this->double(16)->check('value > 5.6'), 'double(16) CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . ' NOT NULL', $this->double()->notNull(), 'double(15) NOT NULL'],
            [Schema::TYPE_DECIMAL, $this->decimal(), 'decimal(10,0)'],
            [Schema::TYPE_DECIMAL . '(12,4)', $this->decimal(12, 4), 'decimal(12,4)'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', $this->decimal()->check('value > 5.6'), 'decimal(10,0) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', $this->decimal(12, 4)->check('value > 5.6'), 'decimal(12,4) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', $this->decimal()->notNull(), 'decimal(10,0) NOT NULL'],
            [Schema::TYPE_DATETIME, $this->dateTime(), 'datetime'],
            [Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", $this->dateTime()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "datetime CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', $this->dateTime()->notNull(), 'datetime NOT NULL'],
            [Schema::TYPE_TIMESTAMP, $this->timestamp(), 'timestamp'],
            [Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", $this->timestamp()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "timestamp CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', $this->timestamp()->notNull(), 'timestamp NOT NULL'],
            [Schema::TYPE_TIME, $this->time(), 'time'],
            [Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", $this->time()->check("value BETWEEN '12:00:00' AND '13:01:01'"), "time CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', $this->time()->notNull(), 'time NOT NULL'],
            [Schema::TYPE_DATE, $this->date(), 'date'],
            [Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", $this->date()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "date CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', $this->date()->notNull(), 'date NOT NULL'],
            [Schema::TYPE_BINARY, $this->binary(), 'blob'],
            [Schema::TYPE_BOOLEAN, $this->boolean(), 'smallint'],
            [Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1', $this->boolean()->notNull()->defaultValue(1), 'smallint NOT NULL DEFAULT 1'],
            [Schema::TYPE_MONEY, $this->money(), 'decimal(19,4)'],
            [Schema::TYPE_MONEY . '(16,2)', $this->money(16, 2), 'decimal(16,2)'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', $this->money()->check('value > 0.0'), 'decimal(19,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', $this->money(16, 2)->check('value > 0.0'), 'decimal(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', $this->money()->notNull(), 'decimal(19,4) NOT NULL'],
            [Schema::TYPE_MONEY . ' NOT NULL COMMENT \'this is money column\'', $this->money()->notNull()->comment('this is money column'), 'decimal(19,4) NOT NULL COMMENT \'this is money column\''],
        ];
    }
}
