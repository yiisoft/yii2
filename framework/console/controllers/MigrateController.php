<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Inflector;

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
 * Since 2.0.10 you can use namespaced migrations. In order to enable this feature you should configure [[migrationNamespaces]]
 * property for the controller at application configuration:
 *
 * ```php
 * return [
 *     'controllerMap' => [
 *         'migrate' => [
 *             'class' => 'yii\console\controllers\MigrateController',
 *             'migrationNamespaces' => [
 *                 'app\migrations',
 *                 'some\extension\migrations',
 *             ],
 *             //'migrationPath' => null, // allows to disable not namespaced migration completely
 *         ],
 *     ],
 * ];
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MigrateController extends BaseMigrateController
{
    /**
     * Maximum length of a migration name.
     * @since 2.0.13
     */
    const MAX_NAME_LENGTH = 180;

    /**
     * @var string the name of the table for keeping applied migration information.
     */
    public $migrationTable = '{{%migration}}';
    /**
     * {@inheritdoc}
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
        'create_junction' => '@yii/views/createTableMigration.php',
    ];
    /**
     * @var bool indicates whether the table names generated should consider
     * the `tablePrefix` setting of the DB connection. For example, if the table
     * name is `post` the generator wil return `{{%post}}`.
     * @since 2.0.8
     */
    public $useTablePrefix = true;
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
     * @var string the comment for the table being created.
     * @since 2.0.14
     */
    public $comment = '';


    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['migrationTable', 'db'], // global for all actions
            $actionID === 'create'
                ? ['templateFile', 'fields', 'useTablePrefix', 'comment']
                : []
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'C' => 'comment',
            'f' => 'fields',
            'p' => 'migrationPath',
            't' => 'migrationTable',
            'F' => 'templateFile',
            'P' => 'useTablePrefix',
            'c' => 'compact',
        ]);
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the [[migrationPath]].
     * @param \yii\base\Action $action the action to be executed.
     * @return bool whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->db = Instance::ensure($this->db, Connection::className());
            return true;
        }

        return false;
    }

    /**
     * Creates a new migration instance.
     * @param string $class the migration class name
     * @return \yii\db\Migration the migration instance
     */
    protected function createMigration($class)
    {
        $this->includeMigrationFile($class);

        return Yii::createObject([
            'class' => $class,
            'db' => $this->db,
            'compact' => $this->compact,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMigrationHistory($limit)
    {
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            $this->createMigrationHistoryTable();
        }
        $query = (new Query())
            ->select(['version', 'apply_time'])
            ->from($this->migrationTable)
            ->orderBy(['apply_time' => SORT_DESC, 'version' => SORT_DESC]);

        if (empty($this->migrationNamespaces)) {
            $query->limit($limit);
            $rows = $query->all($this->db);
            $history = ArrayHelper::map($rows, 'version', 'apply_time');
            unset($history[self::BASE_MIGRATION]);
            return $history;
        }

        $rows = $query->all($this->db);

        $history = [];
        foreach ($rows as $key => $row) {
            if ($row['version'] === self::BASE_MIGRATION) {
                continue;
            }
            if (preg_match('/m?(\d{6}_?\d{6})(\D.*)?$/is', $row['version'], $matches)) {
                $time = str_replace('_', '', $matches[1]);
                $row['canonicalVersion'] = $time;
            } else {
                $row['canonicalVersion'] = $row['version'];
            }
            $row['apply_time'] = (int) $row['apply_time'];
            $history[] = $row;
        }

        usort($history, function ($a, $b) {
            if ($a['apply_time'] === $b['apply_time']) {
                if (($compareResult = strcasecmp($b['canonicalVersion'], $a['canonicalVersion'])) !== 0) {
                    return $compareResult;
                }

                return strcasecmp($b['version'], $a['version']);
            }

            return ($a['apply_time'] > $b['apply_time']) ? -1 : +1;
        });

        $history = array_slice($history, 0, $limit);

        $history = ArrayHelper::map($history, 'version', 'apply_time');

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
            'version' => 'varchar(' . static::MAX_NAME_LENGTH . ') NOT NULL PRIMARY KEY',
            'apply_time' => 'integer',
        ])->execute();
        $this->db->createCommand()->insert($this->migrationTable, [
            'version' => self::BASE_MIGRATION,
            'apply_time' => time(),
        ])->execute();
        $this->stdout("Done.\n", Console::FG_GREEN);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     * @since 2.0.13
     */
    protected function truncateDatabase()
    {
        $db = $this->db;
        $schemas = $db->schema->getTableSchemas();

        // First drop all foreign keys,
        foreach ($schemas as $schema) {
            foreach ($schema->foreignKeys as $name => $foreignKey) {
                $db->createCommand()->dropForeignKey($name, $schema->name)->execute();
                $this->stdout("Foreign key $name dropped.\n");
            }
        }

        // Then drop the tables:
        foreach ($schemas as $schema) {
            try {
                $db->createCommand()->dropTable($schema->name)->execute();
                $this->stdout("Table {$schema->name} dropped.\n");
            } catch (\Exception $e) {
                if ($this->isViewRelated($e->getMessage())) {
                    $db->createCommand()->dropView($schema->name)->execute();
                    $this->stdout("View {$schema->name} dropped.\n");
                } else {
                    $this->stdout("Cannot drop {$schema->name} Table .\n");
                }
            }
        }
    }

    /**
     * Determines whether the error message is related to deleting a view or not
     * @param string $errorMessage
     * @return bool
     */
    private function isViewRelated($errorMessage)
    {
        $dropViewErrors = [
            'DROP VIEW to delete view', // SQLite
            'SQLSTATE[42S02]', // MySQL
        ];

        foreach ($dropViewErrors as $dropViewError) {
            if (strpos($errorMessage, $dropViewError) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function removeMigrationHistory($version)
    {
        $command = $this->db->createCommand();
        $command->delete($this->migrationTable, [
            'version' => $version,
        ])->execute();
    }

    private $_migrationNameLimit;

    /**
     * {@inheritdoc}
     * @since 2.0.13
     */
    protected function getMigrationNameLimit()
    {
        if ($this->_migrationNameLimit !== null) {
            return $this->_migrationNameLimit;
        }
        $tableSchema = $this->db->schema ? $this->db->schema->getTableSchema($this->migrationTable, true) : null;
        if ($tableSchema !== null) {
            return $this->_migrationNameLimit = $tableSchema->columns['version']->size;
        }

        return static::MAX_NAME_LENGTH;
    }

    /**
     * Normalizes table name for generator.
     * When name is preceded with underscore name case is kept - otherwise it's converted from camelcase to underscored.
     * Last underscore is always trimmed so if there should be underscore at the end of name use two of them.
     * @param string $name
     * @return string
     */
    private function normalizeTableName($name)
    {
        if (substr($name, -1) === '_') {
            $name = substr($name, 0, -1);
        }

        if (strncmp($name, '_', 1) === 0) {
            return substr($name, 1);
        }

        return Inflector::underscore($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    protected function generateMigrationSourceCode($params)
    {
        $parsedFields = $this->parseFields();
        $fields = $parsedFields['fields'];
        $foreignKeys = $parsedFields['foreignKeys'];

        $name = $params['name'];
        if ($params['namespace']) {
            $name = substr($name, (strrpos($name, '\\') ?: -1) + 1);
        }

        $templateFile = $this->templateFile;
        $table = null;
        if (preg_match('/^create_?junction_?(?:table)?_?(?:for)?(.+)_?and(.+)_?tables?$/i', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['create_junction'];
            $firstTable = $this->normalizeTableName($matches[1]);
            $secondTable = $this->normalizeTableName($matches[2]);

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

            $foreignKeys[$firstTable . '_id']['table'] = $firstTable;
            $foreignKeys[$secondTable . '_id']['table'] = $secondTable;
            $foreignKeys[$firstTable . '_id']['column'] = null;
            $foreignKeys[$secondTable . '_id']['column'] = null;
            $table = $firstTable . '_' . $secondTable;
        } elseif (preg_match('/^add(.+)columns?_?to(.+)table$/i', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['add_column'];
            $table = $this->normalizeTableName($matches[2]);
        } elseif (preg_match('/^drop(.+)columns?_?from(.+)table$/i', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['drop_column'];
            $table = $this->normalizeTableName($matches[2]);
        } elseif (preg_match('/^create(.+)table$/i', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['create_table'];
            $table = $this->normalizeTableName($matches[1]);
        } elseif (preg_match('/^drop(.+)table$/i', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['drop_table'];
            $table = $this->normalizeTableName($matches[1]);
        }

        foreach ($foreignKeys as $column => $foreignKey) {
            $relatedColumn = $foreignKey['column'];
            $relatedTable = $foreignKey['table'];
            // Since 2.0.11 if related column name is not specified,
            // we're trying to get it from table schema
            // @see https://github.com/yiisoft/yii2/issues/12748
            if ($relatedColumn === null) {
                $relatedColumn = 'id';
                try {
                    $this->db = Instance::ensure($this->db, Connection::className());
                    $relatedTableSchema = $this->db->getTableSchema($relatedTable);
                    if ($relatedTableSchema !== null) {
                        $primaryKeyCount = count($relatedTableSchema->primaryKey);
                        if ($primaryKeyCount === 1) {
                            $relatedColumn = $relatedTableSchema->primaryKey[0];
                        } elseif ($primaryKeyCount > 1) {
                            $this->stdout("Related table for field \"{$column}\" exists, but primary key is composite. Default name \"id\" will be used for related field\n", Console::FG_YELLOW);
                        } elseif ($primaryKeyCount === 0) {
                            $this->stdout("Related table for field \"{$column}\" exists, but does not have a primary key. Default name \"id\" will be used for related field.\n", Console::FG_YELLOW);
                        }
                    }
                } catch (\ReflectionException $e) {
                    $this->stdout("Cannot initialize database component to try reading referenced table schema for field \"{$column}\". Default name \"id\" will be used for related field.\n", Console::FG_YELLOW);
                }
            }
            $foreignKeys[$column] = [
                'idx' => $this->generateTableName("idx-$table-$column"),
                'fk' => $this->generateTableName("fk-$table-$column"),
                'relatedTable' => $this->generateTableName($relatedTable),
                'relatedColumn' => $relatedColumn,
            ];
        }

        return $this->renderFile(Yii::getAlias($templateFile), array_merge($params, [
            'table' => $this->generateTableName($table),
            'fields' => $fields,
            'foreignKeys' => $foreignKeys,
            'tableComment' => $this->comment,
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
     * Parse the command line migration fields.
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
            $chunks = $this->splitFieldIntoChunks($field);
            $property = array_shift($chunks);

            foreach ($chunks as $i => &$chunk) {
                if (strncmp($chunk, 'foreignKey', 10) === 0) {
                    preg_match('/foreignKey\((\w*)\s?(\w*)\)/', $chunk, $matches);
                    $foreignKeys[$property] = [
                        'table' => isset($matches[1])
                            ? $matches[1]
                            : preg_replace('/_id$/', '', $property),
                        'column' => !empty($matches[2])
                            ? $matches[2]
                            : null,
                    ];

                    unset($chunks[$i]);
                    continue;
                }

                if (!preg_match('/^(.+?)\(([^(]+)\)$/', $chunk)) {
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
     * Splits field into chunks
     *
     * @param string $field
     * @return string[]|false
     */
    protected function splitFieldIntoChunks($field)
    {
        $originalDefaultValue = null;
        $defaultValue = null;
        preg_match_all('/defaultValue\(["\'].*?:?.*?["\']\)/', $field, $matches, PREG_SET_ORDER, 0);
        if (isset($matches[0][0])) {
            $originalDefaultValue = $matches[0][0];
            $defaultValue = str_replace(':', '{{colon}}', $originalDefaultValue);
            $field = str_replace($originalDefaultValue, $defaultValue, $field);
        }

        $chunks = preg_split('/\s?:\s?/', $field);

        if (is_array($chunks) && $defaultValue !== null && $originalDefaultValue !== null) {
            foreach ($chunks as $key => $chunk) {
                $chunks[$key] = str_replace($defaultValue, $originalDefaultValue, $chunk);
            }
        }

        return $chunks;
    }

    /**
     * Adds default primary key to fields list if there's no primary key specified.
     * @param array $fields parsed fields
     * @since 2.0.7
     */
    protected function addDefaultPrimaryKey(&$fields)
    {
        foreach ($fields as $field) {
            if ($field['property'] === 'id' || false !== strripos($field['decorators'], 'primarykey()')) {
                return;
            }
        }
        array_unshift($fields, ['property' => 'id', 'decorators' => 'primaryKey()']);
    }
}
