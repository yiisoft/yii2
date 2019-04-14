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
 * 管理应用程序迁移。
 *
 * 迁移意味着对应用程序环境的一组持久更改，
 * 这些更改在不同的开发人员之间共享。例如，在数据库支持的
 * 应用程序中，迁移可能指对数据库的一组更改，
 * 例如创建一个新表，添加一个新表列。
 *
 * 此命令提供跟踪迁移历史记录的支持，
 * 通过迁移进行升级或下载，并创建新的迁移骨架。
 *
 * 迁移历史记录存储在数据库表中
 * 称作 [[migrationTable]]。该表将在第一次执行此命令时自动创建，
 * 如果它不存在的话。您也可以手动创建它，
 * 如下所示：
 *
 * ```sql
 * CREATE TABLE migration (
 *     version varchar(180) PRIMARY KEY,
 *     apply_time integer
 * )
 * ```
 *
 * 以下是此命令的一些常见用法：
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
 * 从 2.0.10 开始，你可以使用带命名空间的迁移。要启用此功能，您应在应用程序配置中为控制器配置 [[migrationNamespaces]]
 * 属性：
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
     * 迁移名称的最大长度。
     * @since 2.0.13
     */
    const MAX_NAME_LENGTH = 180;

    /**
     * @var string 用于保存应用的迁移信息的表的名称。
     */
    public $migrationTable = '{{%migration}}';
    /**
     * {@inheritdoc}
     */
    public $templateFile = '@yii/views/migration.php';
    /**
     * @var array 用于自动生成迁移代码的一组模板路径。
     *
     * 键是模板类型，值是路径或别名。支持的类型是：
     * - `create_table`: 表创建模板
     * - `drop_table`: 表删除模版
     * - `add_column`: 添加新的列模板
     * - `drop_column`: 删除列模板
     * - `create_junction`: 创建连接模板
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
     * @var bool 指示生成的表名是否应考虑
     * DB连接的 `tablePrefix` 设置。例如，如果表名为
     * `post` 生成器将返回 `{{%post}}`。
     * @since 2.0.8
     */
    public $useTablePrefix = false;
    /**
     * @var array 列定义字符串，用于创建迁移代码。
     *
     * 每个定义的格式为 `COLUMN_NAME:COLUMN_TYPE:COLUMN_DECORATOR`。分隔符是 `,`。
     * 例如，`--fields="name:string(12):notNull:unique"`
     * 生成大小为 12 的字符串列，该列不是 null 且唯一的值。
     *
     * 注意：主键是自动添加的，默认情况下名为 id。
     * 如果你想使用另一个名称，则可以明确地指定它比如
     * `--fields="id_key:primaryKey,name:string(12):notNull:unique"`
     * @since 2.0.7
     */
    public $fields = [];
    /**
     * @var Connection|array|string 应用迁移时要使用的 DB 连接对象或应用程序组件 ID。
     * 从版本 2.0.3 开始，这也可以是配置数组
     * 用于创建对象。
     */
    public $db = 'db';
    /**
     * @var string 正在创建的表的注释。
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
     * 此方法是在执行操作之前（在所有可能的过滤器之后）调用的。
     * 它检查 [[migrationPath]] 的存在。
     * @param \yii\base\Action $action 要执行的动作。
     * @return bool 是否应继续执行该动作。
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
     * 创建新的迁移实例。
     * @param string $class 迁移类名称
     * @return \yii\db\Migration 迁移实例
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
     * 创建迁移历史表。
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
            if ($schema->foreignKeys) {
                foreach ($schema->foreignKeys as $name => $foreignKey) {
                    $db->createCommand()->dropForeignKey($name, $schema->name)->execute();
                    $this->stdout("Foreign key $name dropped.\n");
                }
            }
        }

        // Then drop the tables:
        foreach ($schemas as $schema) {
            try {
                $db->createCommand()->dropTable($schema->name)->execute();
                $this->stdout("Table {$schema->name} dropped.\n");
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'DROP VIEW to delete view') !== false) {
                    $db->createCommand()->dropView($schema->name)->execute();
                    $this->stdout("View {$schema->name} dropped.\n");
                } else {
                    $this->stdout("Cannot drop {$schema->name} Table .\n");
                }
            }
        }
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
     * {@inheritdoc}
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
        if (preg_match('/^create_junction(?:_table_for_|_for_|_)(.+)_and_(.+)_tables?$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['create_junction'];
            $firstTable = $matches[1];
            $secondTable = $matches[2];

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
        } elseif (preg_match('/^add_(.+)_columns?_to_(.+)_table$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['add_column'];
            $table = $matches[2];
        } elseif (preg_match('/^drop_(.+)_columns?_from_(.+)_table$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['drop_column'];
            $table = $matches[2];
        } elseif (preg_match('/^create_(.+)_table$/', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['create_table'];
            $table = $matches[1];
        } elseif (preg_match('/^drop_(.+)_table$/', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['drop_table'];
            $table = $matches[1];
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
     * 如果 `useTablePrefix` 等于 true，然后表名将包含
     * 前缀格式。
     *
     * @param string $tableName 要生成的表名。
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
     * 解析命令行迁移字段。
     * @return array 使用以下字段分析结果：
     *
     * - fields: array，解析字段
     * - foreignKeys: array，检测到的外键
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
     * 如果未指定主键，则将默认主键添加到字段列表中。
     * @param array $fields 解析字段
     * @since 2.0.7
     */
    protected function addDefaultPrimaryKey(&$fields)
    {
        foreach ($fields as $field) {
            if (false !== strripos($field['decorators'], 'primarykey()')) {
                return;
            }
        }
        array_unshift($fields, ['property' => 'id', 'decorators' => 'primaryKey()']);
    }
}
