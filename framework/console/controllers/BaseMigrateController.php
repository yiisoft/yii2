<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\db\MigrationInterface;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * BaseMigrateController 是迁移控制器的基类。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class BaseMigrateController extends Controller
{
    /**
     * 虚拟迁移的名称，标记整个迁移历史记录的开头。
     */
    const BASE_MIGRATION = 'm000000_000000_base';

    /**
     * @var string 默认的命令操作。
     */
    public $defaultAction = 'up';
    /**
     * @var string|array 包含迁移类的目录。这可以是
     * 一个 [path alias](guide:concept-aliases) 或一个目录路径。
     *
     * 位于此路径的迁移类应该在没有命名空间的情况下声明。
     * 使用 [[migrationNamespaces]] 属性以防你使用命名空间迁移。
     *
     * 如果你已经设置了 [[migrationNamespaces]]，你可以按顺序将此字段设置为 `null`
     * 禁用未命名空间的迁移的使用。
     *
     * 从版本 2.0.12 开始您还可以指定应该搜索的迁移路径数组
     * 以加载迁移。这主要用于支持在没有命名空间的情况下
     * 提供迁移的旧扩展，并在保留现有迁移的同时采用命名空间迁移的新功能。
     *
     * 通常，要从不同位置加载迁移，[[migrationNamespaces]] 是首选解决方案
     * 因为迁移名称包含历史记录中迁移的来源，而使用多个迁移路径时
     * 则不是这种情况。
     *
     * @see $migrationNamespaces
     */
    public $migrationPath = ['@app/migrations'];
    /**
     * @var array 包含迁移类的命名空间列表。
     *
     * 如果前缀为 `@` 迁移命名空间应该可解析为 [path alias](guide:concept-aliases)，例如如果你指定
     * 命名空间 `app\migrations`，代码 `Yii::getAlias('@app/migrations')` 应该能够返回
     * 此命名空间引用的目录的文件路径。
     * 这与Yii的 [autoloading conventions](guide:concept-autoloading) 相适应。
     *
     * 例如：
     *
     * ```php
     * [
     *     'app\migrations',
     *     'some\extension\migrations',
     * ]
     * ```
     *
     * @since 2.0.10
     * @see $migrationPath
     */
    public $migrationNamespaces = [];
    /**
     * @var string 用于生成新迁移的模板文件。
     * 这可以是一个 [path alias](guide:concept-aliases)（例如 "@app/migrations/template.php"）
     * 或文件路径。
     */
    public $templateFile;
    /**
     * @var bool 指示是否应该压缩控制台输出。
     * 如果设置为 true，则迁移中运行的各个命令将不会输出到控制台。
     * 默认为 false，换句话说输出是完全详细的。
     * @since 2.0.13
     */
    public $compact = false;


    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['migrationPath', 'migrationNamespaces', 'compact'], // 所有动作的全局
            $actionID === 'create' ? ['templateFile'] : [] // 动作创建
        );
    }

    /**
     * 在执行动作之前调用此方法（在所有可能的过滤器之后。）
     * 它检查 [[migrationPath]] 的存在。
     * @param \yii\base\Action $action 要执行的动作。
     * @throws InvalidConfigException 如果在 migrationPath 中指定的目录不存在并且动作不是 "create"。
     * @return bool 是否应该继续执行该动作。
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (empty($this->migrationNamespaces) && empty($this->migrationPath)) {
                throw new InvalidConfigException('At least one of `migrationPath` or `migrationNamespaces` should be specified.');
            }

            foreach ($this->migrationNamespaces as $key => $value) {
                $this->migrationNamespaces[$key] = trim($value, '\\');
            }

            if (is_array($this->migrationPath)) {
                foreach ($this->migrationPath as $i => $path) {
                    $this->migrationPath[$i] = Yii::getAlias($path);
                }
            } elseif ($this->migrationPath !== null) {
                $path = Yii::getAlias($this->migrationPath);
                if (!is_dir($path)) {
                    if ($action->id !== 'create') {
                        throw new InvalidConfigException("Migration failed. Directory specified in migrationPath doesn't exist: {$this->migrationPath}");
                    }
                    FileHelper::createDirectory($path);
                }
                $this->migrationPath = $path;
            }

            $version = Yii::getVersion();
            $this->stdout("Yii Migration Tool (based on Yii v{$version})\n\n");

            return true;
        }

        return false;
    }

    /**
     * 通过应用新迁移来升级应用程序。
     *
     * 例如，
     *
     * ```
     * yii migrate     # apply all new migrations
     * yii migrate 3   # apply the first 3 new migrations
     * ```
     *
     * @param int $limit 要应用的新迁移数。如果是 0，
     * 意味着应用所有可用的新迁移。
     *
     * @return int 动作执行的状态。0 表示正常，其他值表示异常。
     */
    public function actionUp($limit = 0)
    {
        $migrations = $this->getNewMigrations();
        if (empty($migrations)) {
            $this->stdout("No new migrations found. Your system is up-to-date.\n", Console::FG_GREEN);

            return ExitCode::OK;
        }

        $total = count($migrations);
        $limit = (int) $limit;
        if ($limit > 0) {
            $migrations = array_slice($migrations, 0, $limit);
        }

        $n = count($migrations);
        if ($n === $total) {
            $this->stdout("Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
        } else {
            $this->stdout("Total $n out of $total new " . ($total === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
        }

        foreach ($migrations as $migration) {
            $nameLimit = $this->getMigrationNameLimit();
            if ($nameLimit !== null && strlen($migration) > $nameLimit) {
                $this->stdout("\nThe migration name '$migration' is too long. Its not possible to apply this migration.\n", Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");

        $applied = 0;
        if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateUp($migration)) {
                    $this->stdout("\n$applied from $n " . ($applied === 1 ? 'migration was' : 'migrations were') . " applied.\n", Console::FG_RED);
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return ExitCode::UNSPECIFIED_ERROR;
                }
                $applied++;
            }

            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') . " applied.\n", Console::FG_GREEN);
            $this->stdout("\nMigrated up successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * 通过恢复旧迁移来降级应用程序。
     *
     * 例如，
     *
     * ```
     * yii migrate/down     # 恢复上次迁移
     * yii migrate/down 3   # 恢复最后 3 次迁移
     * yii migrate/down all # 恢复所有迁移
     * ```
     *
     * @param int|string $limit 要还原的迁移次数。默认为 1，
     * 表示将恢复上次应用的迁移。当值为 “all” 时，将还原所有迁移。
     * @throws Exception 如果指定的步数小于 1。
     *
     * @return int 动作执行的状态。0 表示正常，其他值表示异常。
     */
    public function actionDown($limit = 1)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int) $limit;
            if ($limit < 1) {
                throw new Exception('The step argument must be greater than 0.');
            }
        }

        $migrations = $this->getMigrationHistory($limit);

        if (empty($migrations)) {
            $this->stdout("No migration has been done before.\n", Console::FG_YELLOW);

            return ExitCode::OK;
        }

        $migrations = array_keys($migrations);

        $n = count($migrations);
        $this->stdout("Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be reverted:\n", Console::FG_YELLOW);
        foreach ($migrations as $migration) {
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");

        $reverted = 0;
        if ($this->confirm('Revert the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateDown($migration)) {
                    $this->stdout("\n$reverted from $n " . ($reverted === 1 ? 'migration was' : 'migrations were') . " reverted.\n", Console::FG_RED);
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return ExitCode::UNSPECIFIED_ERROR;
                }
                $reverted++;
            }
            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') . " reverted.\n", Console::FG_GREEN);
            $this->stdout("\nMigrated down successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * 重做最后几次迁移。
     *
     * 此命令将首先还原指定的迁移, 然后再次应用
     * 他们。比如，
     *
     * ```
     * yii migrate/redo     # 重做上次应用的迁移
     * yii migrate/redo 3   # 重做最后 3 次应用的迁移
     * yii migrate/redo all # 重做所有迁移
     * ```
     *
     * @param int|string $limit 要重做的迁移次数。默认为 1，
     * 表示最后一次应用的迁移将重做。当等于 "all" 时，将重做所有迁移。
     * @throws Exception 如果指定的步数小于 1。
     *
     * @return int 动作执行的状态。0 表示正常，其他值表示异常。
     */
    public function actionRedo($limit = 1)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int) $limit;
            if ($limit < 1) {
                throw new Exception('The step argument must be greater than 0.');
            }
        }

        $migrations = $this->getMigrationHistory($limit);

        if (empty($migrations)) {
            $this->stdout("No migration has been done before.\n", Console::FG_YELLOW);

            return ExitCode::OK;
        }

        $migrations = array_keys($migrations);

        $n = count($migrations);
        $this->stdout("Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be redone:\n", Console::FG_YELLOW);
        foreach ($migrations as $migration) {
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");

        if ($this->confirm('Redo the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateDown($migration)) {
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return ExitCode::UNSPECIFIED_ERROR;
                }
            }
            foreach (array_reverse($migrations) as $migration) {
                if (!$this->migrateUp($migration)) {
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return ExitCode::UNSPECIFIED_ERROR;
                }
            }
            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') . " redone.\n", Console::FG_GREEN);
            $this->stdout("\nMigration redone successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * 升级或降级至指定版本。
     *
     * 也可以将版本降级到过去的某个应用时间，通过提供
     * 一个 UNIX 时间戳或者一个 strtotime() 函数可解析的字符串。这意味着
     * 在指定的特定时间之后应用的所有版本都将被还原。
     *
     * 此命令将首先还原指定的迁移，然后再次应用他们。
     * 比如，
     *
     * ```
     * yii migrate/to 101129_185401                          # 使用时间戳
     * yii migrate/to m101129_185401_create_user_table       # 使用全名
     * yii migrate/to 1392853618                             # 使用 UNIX 时间戳
     * yii migrate/to "2014-02-15 13:00:50"                  # 使用 strtotime() 可解析的字符串
     * yii migrate/to app\migrations\M101129185401CreateUser # 使用完整的命名空间名称
     * ```
     *
     * @param string $version 应用将迁移到的
     * 过去的版本名称或特定的时间值。这可以是时间戳，
     * 迁移的全名，UNIX 时间戳或，或可解析的时间日期
     * 字符串。
     * @throws Exception 如果 version 参数无效。
     */
    public function actionTo($version)
    {
        if (($namespaceVersion = $this->extractNamespaceMigrationVersion($version)) !== false) {
            $this->migrateToVersion($namespaceVersion);
        } elseif (($migrationName = $this->extractMigrationVersion($version)) !== false) {
            $this->migrateToVersion($migrationName);
        } elseif ((string) (int) $version == $version) {
            $this->migrateToTime($version);
        } elseif (($time = strtotime($version)) !== false) {
            $this->migrateToTime($time);
        } else {
            throw new Exception("The version argument must be either a timestamp (e.g. 101129_185401),\n the full name of a migration (e.g. m101129_185401_create_user_table),\n the full namespaced name of a migration (e.g. app\\migrations\\M101129185401CreateUserTable),\n a UNIX timestamp (e.g. 1392853000), or a datetime string parseable\nby the strtotime() function (e.g. 2014-02-15 13:00:50).");
        }
    }

    /**
     * 将迁移历史记录修改为指定的版本。
     *
     * 不会进行实际迁移。
     *
     * ```
     * yii migrate/mark 101129_185401                        # 使用时间戳
     * yii migrate/mark m101129_185401_create_user_table     # 使用全名
     * yii migrate/mark app\migrations\M101129185401CreateUser # 使用命名空间全名
     * yii migrate/mark m000000_000000_base # 重置完整的迁移历史记录
     * ```
     *
     * @param string $version 应标记迁移历史记录的版本。
     * 这可以是时间戳或迁移的全名。
     * 你可以指定名称 `m000000_000000_base` 以将迁移历史设置为
     * 未应用迁移的状态。
     * @return int CLI 退出码
     * @throws Exception 如果版本参数无效或无法找到版本。
     */
    public function actionMark($version)
    {
        $originalVersion = $version;
        if (($namespaceVersion = $this->extractNamespaceMigrationVersion($version)) !== false) {
            $version = $namespaceVersion;
        } elseif (($migrationName = $this->extractMigrationVersion($version)) !== false) {
            $version = $migrationName;
        } elseif ($version !== static::BASE_MIGRATION) {
            throw new Exception("The version argument must be either a timestamp (e.g. 101129_185401)\nor the full name of a migration (e.g. m101129_185401_create_user_table)\nor the full name of a namespaced migration (e.g. app\\migrations\\M101129185401CreateUserTable).");
        }

        // try mark up
        $migrations = $this->getNewMigrations();
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version) === 0) {
                if ($this->confirm("Set migration history at $originalVersion?")) {
                    for ($j = 0; $j <= $i; ++$j) {
                        $this->addMigrationHistory($migrations[$j]);
                    }
                    $this->stdout("The migration history is set at $originalVersion.\nNo actual migration was performed.\n", Console::FG_GREEN);
                }

                return ExitCode::OK;
            }
        }

        // try mark down
        $migrations = array_keys($this->getMigrationHistory(null));
        $migrations[] = static::BASE_MIGRATION;
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version) === 0) {
                if ($i === 0) {
                    $this->stdout("Already at '$originalVersion'. Nothing needs to be done.\n", Console::FG_YELLOW);
                } else {
                    if ($this->confirm("Set migration history at $originalVersion?")) {
                        for ($j = 0; $j < $i; ++$j) {
                            $this->removeMigrationHistory($migrations[$j]);
                        }
                        $this->stdout("The migration history is set at $originalVersion.\nNo actual migration was performed.\n", Console::FG_GREEN);
                    }
                }

                return ExitCode::OK;
            }
        }

        throw new Exception("Unable to find the version '$originalVersion'.");
    }

    /**
     * 截断整个数据库并从头开始迁移。
     *
     * ```
     * yii migrate/fresh
     * ```
     *
     * @since 2.0.13
     */
    public function actionFresh()
    {
        if (YII_ENV_PROD) {
            $this->stdout("YII_ENV is set to 'prod'.\nRefreshing migrations is not possible on production systems.\n");
            return ExitCode::OK;
        }

        if ($this->confirm(
            "Are you sure you want to reset the database and start the migration from the beginning?\nAll data will be lost irreversibly!")) {
            $this->truncateDatabase();
            $this->actionUp();
        } else {
            $this->stdout('Action was cancelled by user. Nothing has been performed.');
        }
    }

    /**
     * 检查给定的迁移版本规范是否与命名空间迁移名称匹配。
     * @param string $rawVersion 从用户输入接收的原始版本规范。
     * @return string|false 实际迁移版本，`false` - 如果不匹配。
     * @since 2.0.10
     */
    private function extractNamespaceMigrationVersion($rawVersion)
    {
        if (preg_match('/^\\\\?([\w_]+\\\\)+m(\d{6}_?\d{6})(\D.*)?$/is', $rawVersion, $matches)) {
            return trim($rawVersion, '\\');
        }

        return false;
    }

    /**
     * 检查给定的迁移版本规范是否与迁移基本名称匹配。
     * @param string $rawVersion 从用户输入接收的原始版本规范。
     * @return string|false 实际迁移版本，`false` - 如果不匹配。
     * @since 2.0.10
     */
    private function extractMigrationVersion($rawVersion)
    {
        if (preg_match('/^m?(\d{6}_?\d{6})(\D.*)?$/is', $rawVersion, $matches)) {
            return 'm' . $matches[1];
        }

        return false;
    }

    /**
     * 显示迁移历史记录。
     *
     * 此命令将显示已应用的迁移列表到目前为止。
     * 例如，
     *
     * ```
     * yii migrate/history     # 显示最近 10 次迁移
     * yii migrate/history 5   # 显示最近 5 次迁移
     * yii migrate/history all # 显示整个历史记录
     * ```
     *
     * @param int|string $limit 要显示的最大迁移数。
     * 如果是 "all"，将显示整个迁移历史记录。
     * @throws \yii\console\Exception 如果传递了无效限制值。
     */
    public function actionHistory($limit = 10)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int) $limit;
            if ($limit < 1) {
                throw new Exception('The limit must be greater than 0.');
            }
        }

        $migrations = $this->getMigrationHistory($limit);

        if (empty($migrations)) {
            $this->stdout("No migration has been done before.\n", Console::FG_YELLOW);
        } else {
            $n = count($migrations);
            if ($limit > 0) {
                $this->stdout("Showing the last $n applied " . ($n === 1 ? 'migration' : 'migrations') . ":\n", Console::FG_YELLOW);
            } else {
                $this->stdout("Total $n " . ($n === 1 ? 'migration has' : 'migrations have') . " been applied before:\n", Console::FG_YELLOW);
            }
            foreach ($migrations as $version => $time) {
                $this->stdout("\t(" . date('Y-m-d H:i:s', $time) . ') ' . $version . "\n");
            }
        }
    }

    /**
     * 显示未应用的新迁移。
     *
     * 此命令将显示尚未应用的新迁移。
     * 例如，
     *
     * ```
     * yii migrate/new     # 显示前 10 次新迁移
     * yii migrate/new 5   # 显示前 5 次新迁移
     * yii migrate/new all # 显示所有新迁移
     * ```
     *
     * @param int|string $limit 要显示的最大新迁移数。
     * 如果是 `all`，将显示所有可用的新迁移。
     * @throws \yii\console\Exception 如果传递了无效限制值。
     */
    public function actionNew($limit = 10)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int) $limit;
            if ($limit < 1) {
                throw new Exception('The limit must be greater than 0.');
            }
        }

        $migrations = $this->getNewMigrations();

        if (empty($migrations)) {
            $this->stdout("No new migrations found. Your system is up-to-date.\n", Console::FG_GREEN);
        } else {
            $n = count($migrations);
            if ($limit && $n > $limit) {
                $migrations = array_slice($migrations, 0, $limit);
                $this->stdout("Showing $limit out of $n new " . ($n === 1 ? 'migration' : 'migrations') . ":\n", Console::FG_YELLOW);
            } else {
                $this->stdout("Found $n new " . ($n === 1 ? 'migration' : 'migrations') . ":\n", Console::FG_YELLOW);
            }

            foreach ($migrations as $migration) {
                $this->stdout("\t" . $migration . "\n");
            }
        }
    }

    /**
     * 创建新的迁移。
     *
     * 此命令使用可用的迁移模板创建新迁移。
     * 使用此命令后，开发人员应通过填充实际的迁移逻辑
     * 来修改创建的迁移框架。
     *
     * ```
     * yii migrate/create create_user_table
     * ```
     *
     * 为了生成命名空间迁移，您应该在迁移名称之前指定命名空间。
     * 请注意反斜杠（`\`）通常被认为是 shell 中的特殊字符，因此您需要将其转义
     * 正确避免 shell 错误或不正确的行为。
     * 例如：
     *
     * ```
     * yii migrate/create 'app\\migrations\\createUserTable'
     * ```
     *
     * 如果未设置 [[migrationPath]] 且未提供命名空间，则将使用 [[migrationNamespaces]]的第一个条目。
     *
     * @param string $name 新迁移的名称。这应该只包含
     * 字母，数字，下划线和/或反斜杠。
     *
     * 注意：如果迁移名称是特殊形式，例如 create_xxx 或 drop_xxx，
     * 然后生成的迁移文件将包含额外的代码，
     * 在这种情况下用于创建/删除表。
     *
     * @throws Exception 如果 name 参数无效。
     */
    public function actionCreate($name)
    {
        if (!preg_match('/^[\w\\\\]+$/', $name)) {
            throw new Exception('The migration name should contain letters, digits, underscore and/or backslash characters only.');
        }

        list($namespace, $className) = $this->generateClassName($name);
        // Abort if name is too long
        $nameLimit = $this->getMigrationNameLimit();
        if ($nameLimit !== null && strlen($className) > $nameLimit) {
            throw new Exception('The migration name is too long.');
        }

        $migrationPath = $this->findMigrationPath($namespace);

        $file = $migrationPath . DIRECTORY_SEPARATOR . $className . '.php';
        if ($this->confirm("Create new migration '$file'?")) {
            $content = $this->generateMigrationSourceCode([
                'name' => $name,
                'className' => $className,
                'namespace' => $namespace,
            ]);
            FileHelper::createDirectory($migrationPath);
            file_put_contents($file, $content, LOCK_EX);
            $this->stdout("New migration created successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * 生成类的基本名称和命名空间通过用户输入的迁移名称。
     * @param string $name 用户输入的迁移名称。
     * @return array 2 个元素列表：'namespace' 和 'class base name'
     * @since 2.0.10
     */
    private function generateClassName($name)
    {
        $namespace = null;
        $name = trim($name, '\\');
        if (strpos($name, '\\') !== false) {
            $namespace = substr($name, 0, strrpos($name, '\\'));
            $name = substr($name, strrpos($name, '\\') + 1);
        } else {
            if ($this->migrationPath === null) {
                $migrationNamespaces = $this->migrationNamespaces;
                $namespace = array_shift($migrationNamespaces);
            }
        }

        if ($namespace === null) {
            $class = 'm' . gmdate('ymd_His') . '_' . $name;
        } else {
            $class = 'M' . gmdate('ymdHis') . ucfirst($name);
        }

        return [$namespace, $class];
    }

    /**
     * 查找指定迁移命名空间的文件路径。
     * @param string|null $namespace 迁移命名空间。
     * @return string 迁移文件路径。
     * @throws Exception 失败时。
     * @since 2.0.10
     */
    private function findMigrationPath($namespace)
    {
        if (empty($namespace)) {
            return is_array($this->migrationPath) ? reset($this->migrationPath) : $this->migrationPath;
        }

        if (!in_array($namespace, $this->migrationNamespaces, true)) {
            throw new Exception("Namespace '{$namespace}' not found in `migrationNamespaces`");
        }

        return $this->getNamespacePath($namespace);
    }

    /**
     * 返回与给定命名空间匹配的文件路径。
     * @param string $namespace 命名空间
     * @return string 文件路径
     * @since 2.0.10
     */
    private function getNamespacePath($namespace)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace)));
    }

    /**
     * 使用指定的迁移类进行升级。
     * @param string $class 迁移类名。
     * @return bool 迁移是否成功。
     */
    protected function migrateUp($class)
    {
        if ($class === self::BASE_MIGRATION) {
            return true;
        }

        $this->stdout("*** applying $class\n", Console::FG_YELLOW);
        $start = microtime(true);
        $migration = $this->createMigration($class);
        if ($migration->up() !== false) {
            $this->addMigrationHistory($class);
            $time = microtime(true) - $start;
            $this->stdout("*** applied $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_GREEN);

            return true;
        }

        $time = microtime(true) - $start;
        $this->stdout("*** failed to apply $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_RED);

        return false;
    }

    /**
     * 使用指定的迁移类降级。
     * @param string $class 迁移类名
     * @return bool 迁移是否成功
     */
    protected function migrateDown($class)
    {
        if ($class === self::BASE_MIGRATION) {
            return true;
        }

        $this->stdout("*** reverting $class\n", Console::FG_YELLOW);
        $start = microtime(true);
        $migration = $this->createMigration($class);
        if ($migration->down() !== false) {
            $this->removeMigrationHistory($class);
            $time = microtime(true) - $start;
            $this->stdout("*** reverted $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_GREEN);

            return true;
        }

        $time = microtime(true) - $start;
        $this->stdout("*** failed to revert $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_RED);

        return false;
    }

    /**
     * 创建新的迁移实例。
     * @param string $class 迁移类名
     * @return \yii\db\MigrationInterface 迁移实例
     */
    protected function createMigration($class)
    {
        $this->includeMigrationFile($class);

        /** @var MigrationInterface $migration */
        $migration = Yii::createObject($class);
        if ($migration instanceof BaseObject && $migration->canSetProperty('compact')) {
            $migration->compact = $this->compact;
        }

        return $migration;
    }

    /**
     * 包括给定迁移类名称的迁移文件。
     *
     * 此函数对命名空间迁移不做任何操作，哪些由
     * 自动加载加载。它将包含迁移文件，通过在
     * [[migrationPath]] 中搜索没有命名空间的类。
     * @param string $class 迁移类名。
     * @since 2.0.12
     */
    protected function includeMigrationFile($class)
    {
        $class = trim($class, '\\');
        if (strpos($class, '\\') === false) {
            if (is_array($this->migrationPath)) {
                foreach ($this->migrationPath as $path) {
                    $file = $path . DIRECTORY_SEPARATOR . $class . '.php';
                    if (is_file($file)) {
                        require_once $file;
                        break;
                    }
                }
            } else {
                $file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
                require_once $file;
            }
        }
    }

    /**
     * 迁移到过去指定的应用时间。
     * @param int $time UNIX 时间戳值。
     */
    protected function migrateToTime($time)
    {
        $count = 0;
        $migrations = array_values($this->getMigrationHistory(null));
        while ($count < count($migrations) && $migrations[$count] > $time) {
            ++$count;
        }
        if ($count === 0) {
            $this->stdout("Nothing needs to be done.\n", Console::FG_GREEN);
        } else {
            $this->actionDown($count);
        }
    }

    /**
     * 迁移到特定版本。
     * @param string $version 名称的完整格式。
     * @return int CLI 退出码
     * @throws Exception 如果找不到提供的版本。
     */
    protected function migrateToVersion($version)
    {
        $originalVersion = $version;

        // try migrate up
        $migrations = $this->getNewMigrations();
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version) === 0) {
                $this->actionUp($i + 1);

                return ExitCode::OK;
            }
        }

        // try migrate down
        $migrations = array_keys($this->getMigrationHistory(null));
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version) === 0) {
                if ($i === 0) {
                    $this->stdout("Already at '$originalVersion'. Nothing needs to be done.\n", Console::FG_YELLOW);
                } else {
                    $this->actionDown($i);
                }

                return ExitCode::OK;
            }
        }

        throw new Exception("Unable to find the version '$originalVersion'.");
    }

    /**
     * 返回未应用的迁移。
     * @return array 新迁移列表
     */
    protected function getNewMigrations()
    {
        $applied = [];
        foreach ($this->getMigrationHistory(null) as $class => $time) {
            $applied[trim($class, '\\')] = true;
        }

        $migrationPaths = [];
        if (is_array($this->migrationPath)) {
            foreach ($this->migrationPath as $path) {
                $migrationPaths[] = [$path, ''];
            }
        } elseif (!empty($this->migrationPath)) {
            $migrationPaths[] = [$this->migrationPath, ''];
        }
        foreach ($this->migrationNamespaces as $namespace) {
            $migrationPaths[] = [$this->getNamespacePath($namespace), $namespace];
        }

        $migrations = [];
        foreach ($migrationPaths as $item) {
            list($migrationPath, $namespace) = $item;
            if (!file_exists($migrationPath)) {
                continue;
            }
            $handle = opendir($migrationPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $migrationPath . DIRECTORY_SEPARATOR . $file;
                if (preg_match('/^(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches) && is_file($path)) {
                    $class = $matches[1];
                    if (!empty($namespace)) {
                        $class = $namespace . '\\' . $class;
                    }
                    $time = str_replace('_', '', $matches[2]);
                    if (!isset($applied[$class])) {
                        $migrations[$time . '\\' . $class] = $class;
                    }
                }
            }
            closedir($handle);
        }
        ksort($migrations);

        return array_values($migrations);
    }

    /**
     * 生成新的迁移源 PHP 代码。
     * 子类可以重写此方法，为进程添加额外的逻辑或变动。
     * @param array $params 生成参数，通常存在以下参数：
     *
     *  - name: string 迁移基本名称
     *  - className: string 迁移类名
     *
     * @return string 生成的 PHP 代码。
     * @since 2.0.8
     */
    protected function generateMigrationSourceCode($params)
    {
        return $this->renderFile(Yii::getAlias($this->templateFile), $params);
    }

    /**
     * 截断数据库。
     * 应在子类中覆盖此方法以实现清除数据库的任务。
     * @throws NotSupportedException 如果没有被覆盖
     * @since 2.0.13
     */
    protected function truncateDatabase()
    {
        throw new NotSupportedException('This command is not implemented in ' . get_class($this));
    }

    /**
     * 返回迁移的最大名称长度。
     *
     * 子类可以重写此方法以定义限制。
     * @return int|null 迁移的最大名称长度，如果没有限制则为 `null`。
     * @since 2.0.13
     */
    protected function getMigrationNameLimit()
    {
        return null;
    }

    /**
     * 返回迁移历史记录。
     * @param int $limit 要返回的历史记录中的最大记录数。`null` 表示 "no limit"。
     * @return array 迁移历史记录
     */
    abstract protected function getMigrationHistory($limit);

    /**
     * 将新迁移条目添加到历史记录中。
     * @param string $version 迁移版本名称。
     */
    abstract protected function addMigrationHistory($version);

    /**
     * 从历史记录中删除现有迁移。
     * @param string $version 迁移版本名称。
     */
    abstract protected function removeMigrationHistory($version);
}
