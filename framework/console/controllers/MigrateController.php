<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Manages application migrations.
 *
 * A migration means a set of persistent changes to the application environment
 * that is shared among different developers. For example, in an application
 * backed by a database, a migration may refer to a set of changes to
 * the database, such as creating a new table, adding a new table column.
 *
 * This command provides support for tracking the migration history, upgrading
 * or downloading with migrations, and creating new migration skeletons.
 *
 * The migration history is stored in a database table named
 * as [[migrationTable]]. The table will be automatically created the first time
 * this command is executed, if it does not exist. You may also manually
 * create it as follows:
 *
 * ```sql
 * CREATE TABLE migration (
 *     version varchar(180) PRIMARY KEY,
 *     apply_time integer
 * )
 * ```
 *
 * Below are some common usages of this command:
 *
 * ```
 * # creates a new migration named 'create_user_table'
 * yii migrate/create create_user_table
 *
 * # applies ALL new migrations
 * yii migrate
 *
 * # reverts the last applied migration
 * yii migrate/down
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MigrateController extends BaseMigrateController
{
    /**
     * @var string the name of the table for keeping applied migration information.
     */
    public $migrationTable = '{{%migration}}';
    /**
     * @inheritdoc
     */
    public $templateFile = '@yii/views/migration.php';
    /**
     * @var array a set of template paths for generating migration code automatically.
     *
     * The key is the template type, the value is a path or the alias. Supported types are:
     * - `create_table`: table creating template
     * - `drop_table`: table dropping template
     * - `add_column`: adding new column template
     * - `drop_column`: dropping column template
     * - `create_junction`: create junction template
     *
     * @since 2.0.7
     */
    public $generatorTemplateFiles = [
        'create_table' => '@yii/views/createTableMigration.php',
        'drop_table' => '@yii/views/dropTableMigration.php',
        'add_column' => '@yii/views/addColumnMigration.php',
        'drop_column' => '@yii/views/dropColumnMigration.php',
        'create_junction' => '@yii/views/createTableMigration.php'
    ];
    /**
     * @var boolean indicates whether the table names generated should consider
     * the `tablePrefix` setting of the DB connection. For example, if the table
     * name is `post` the generator wil return `{{%post}}`.
     * @since 2.0.8
     */
    public $useTablePrefix = false;
    /**
     * @var array column definition strings used for creating migration code.
     *
     * The format of each definition is `COLUMN_NAME:COLUMN_TYPE:COLUMN_DECORATOR`. Delimiter is `,`.
     * For example, `--fields="name:string(12):notNull:unique"`
     * produces a string column of size 12 which is not null and unique values.
     *
     * Note: primary key is added automatically and is named id by default.
     * If you want to use another name you may specify it explicitly like
     * `--fields="id_key:primaryKey,name:string(12):notNull:unique"`
     * @since 2.0.7
     */
    public $fields = [];
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection to use
     * when applying migrations. Starting from version 2.0.3, this can also be a configuration array
     * for creating the object.
     */
    public $db = 'db';


    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['migrationTable', 'db'], // global for all actions
            $actionID === 'create'
                ? ['templateFile', 'fields', 'useTablePrefix']
                : []
        );
    }

    /**
     * @inheritdoc
     * @since 2.0.8
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'f' => 'fields',
            'p' => 'migrationPath',
            't' => 'migrationTable',
            'F' => 'templateFile',
            'P' => 'useTablePrefix',
        ]);
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the [[migrationPath]].
     * @param \yii\base\Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($action->id !== 'create') {
                $this->db = Instance::ensure($this->db, Connection::className());
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates a new migration instance.
     * @param string $class the migration class name
     * @return \yii\db\Migration the migration instance
     */
    protected function createMigration($class)
    {
        $file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
        require_once($file);

        return new $class(['db' => $this->db]);
    }

    /**
     * @inheritdoc
     */
    protected function getMigrationHistory($limit)
    {
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            $this->createMigrationHistoryTable();
        }
        $query = new Query;
        $rows = $query->select(['version', 'apply_time'])
            ->from($this->migrationTable)
            ->orderBy('apply_time DESC, version DESC')
            ->limit($limit)
            ->createCommand($this->db)
            ->queryAll();
        $history = ArrayHelper::map($rows, 'version', 'apply_time');
        unset($history[self::BASE_MIGRATION]);

        return $history;
    }

    /**
     * Creates the migration history table.
     */
    protected function createMigrationHistoryTable()
    {
        $tableName = $this->db->schema->getRawTableName($this->migrationTable);
        $this->stdout("Creating migration history table \"$tableName\"...", Console::FG_YELLOW);
        $this->db->createCommand()->createTable($this->migrationTable, [
            'version' => 'varchar(180) NOT NULL PRIMARY KEY',
            'apply_time' => 'integer',
        ])->execute();
        $this->db->createCommand()->insert($this->migrationTable, [
            'version' => self::BASE_MIGRATION,
            'apply_time' => time(),
        ])->execute();
        $this->stdout("Done.\n", Console::FG_GREEN);
    }

    /**
     * @inheritdoc
     */
    protected function addMigrationHistory($version)
    {
        $command = $this->db->createCommand();
        $command->insert($this->migrationTable, [
            'version' => $version,
            'apply_time' => time(),
        ])->execute();
    }

    /**
     * @inheritdoc
     */
    protected function removeMigrationHistory($version)
    {
        $command = $this->db->createCommand();
        $command->delete($this->migrationTable, [
            'version' => $version,
        ])->execute();
    }

    /**
     * @inheritdoc
     * @since 2.0.8
     */
    protected function generateMigrationSourceCode($params)
    {
        $parsedFields = $this->parseFields();
        $fields = $parsedFields['fields'];
        $foreignKeys = $parsedFields['foreignKeys'];

        $name = $params['name'];

        $templateFile = $this->templateFile;
        $table = null;
        if (preg_match('/^create_junction_(.+)_and_(.+)$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['create_junction'];
            $firstTable = mb_strtolower($matches[1], Yii::$app->charset);
            $secondTable = mb_strtolower($matches[2], Yii::$app->charset);

            $fields = array_merge(
                [
                    [
                        'property' => $firstTable . '_id',
                        'decorators' => 'integer()',
                    ],
                    [
                        'property' => $secondTable . '_id',
                        'decorators' => 'integer()',
                    ],
                ],
                $fields,
                [
                    [
                        'property' => 'PRIMARY KEY(' .
                            $firstTable . '_id, ' .
                            $secondTable . '_id)',
                    ],
                ]
            );

            $foreignKeys[$firstTable . '_id'] = $firstTable;
            $foreignKeys[$secondTable . '_id'] = $secondTable;
            $table = $firstTable . '_' . $secondTable;
        } elseif (preg_match('/^add_(.+)_to_(.+)$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['add_column'];
            $table = mb_strtolower($matches[2], Yii::$app->charset);
        } elseif (preg_match('/^drop_(.+)_from_(.+)$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['drop_column'];
            $table = mb_strtolower($matches[2], Yii::$app->charset);
        } elseif (preg_match('/^create_(.+)$/', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['create_table'];
            $table = mb_strtolower($matches[1], Yii::$app->charset);
        } elseif (preg_match('/^drop_(.+)$/', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['drop_table'];
            $table = mb_strtolower($matches[1], Yii::$app->charset);
        }

        foreach ($foreignKeys as $column => $relatedTable) {
            $foreignKeys[$column] = [
                'idx' => $this->generateTableName("idx-$table-$column"),
                'fk' => $this->generateTableName("fk-$table-$column"),
                'relatedTable' => $this->generateTableName($relatedTable)
            ];
        }

        return $this->renderFile(Yii::getAlias($templateFile), array_merge($params, [
            'table' => $this->generateTableName($table),
            'fields' => $fields,
            'foreignKeys' => $foreignKeys,
        ]));
    }

    /**
     * If `useTablePrefix` equals true, then the table name will contain the
     * prefix format.
     *
     * @param string $tableName the table name to generate.
     * @return string
     * @since 2.0.8
     */
    protected function generateTableName($tableName)
    {
        if (!$this->useTablePrefix) {
            return $tableName;
        }
        return '{{%' . $tableName . '}}';
    }

    /**
     * Parse the command line migration fields
     * @return array parse result with following fields:
     *
     * - fields: array, parsed fields
     * - foreignKeys: array, detected foreign keys
     *
     * @since 2.0.7
     */
    protected function parseFields()
    {
        $fields = [];
        $foreignKeys = [];

        foreach ($this->fields as $index => $field) {
            $chunks = preg_split('/\s?:\s?/', $field, null);
            $property = array_shift($chunks);

            foreach ($chunks as $i => &$chunk) {
                if (strpos($chunk, 'foreignKey') === 0) {
                    preg_match('/foreignKey\((\w*)\)/', $chunk, $matches);
                    $foreignKeys[$property] = isset($matches[1])
                        ? $matches[1]
                        : preg_replace('/_id$/', '', $property);

                    unset($chunks[$i]);
                    continue;
                }

                if (!preg_match('/^(.+?)\(([^)]+)\)$/', $chunk)) {
                    $chunk .= '()';
                }
            }
            $fields[] = [
                'property' => $property,
                'decorators' => implode('->', $chunks),
            ];
        }

        return [
            'fields' => $fields,
            'foreignKeys' => $foreignKeys,
        ];
    }

    /**
     * Adds default primary key to fields list if there's no primary key specified
     * @param array $fields parsed fields
     * @since 2.0.7
     */
    protected function addDefaultPrimaryKey(&$fields)
    {
        foreach ($fields as $field) {
            if ($field['decorators'] === 'primaryKey()') {
                return;
            }
        }
        array_unshift($fields, ['property' => 'id', 'decorators' => 'primaryKey()']);
    }
}
