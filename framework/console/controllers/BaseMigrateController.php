<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\console\Exception;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * BaseMigrateController is the base class for migrate controllers.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class BaseMigrateController extends Controller
{
    /**
     * The name of the dummy migration that marks the beginning of the whole migration history.
     */
    const BASE_MIGRATION = 'm000000_000000_base';

    /**
     * @var string the default command action.
     */
    public $defaultAction = 'up';
    /**
     * @var string the directory containing the migration classes. This can be either
     * a path alias or a directory path.
     *
     * Migration classes located at this path should be declared without a namespace.
     * Use [[migrationNamespaces]] property in case you are using namespaced migrations.
     *
     * If you have set up [[migrationNamespaces]], you may set this field to `null` in order
     * to disable usage of migrations that are not namespaced.
     */
    public $migrationPath = '@app/migrations';
    /**
     * @var array list of namespaces containing the migration classes.
     *
     * Migration namespaces should be resolvable as a path alias if prefixed with `@`, e.g. if you specify
     * the namespace `app\migrations`, the code `Yii::getAlias('@app/migrations')` should be able to return
     * the file path to the directory this namespace refers to.
     *
     * For example:
     *
     * ```php
     * [
     *     'app\migrations',
     *     'some\extension\migrations',
     * ]
     * ```
     *
     * @since 2.0.10
     */
    public $migrationNamespaces = [];
    /**
     * @var string the template file for generating new migrations.
     * This can be either a path alias (e.g. "@app/migrations/template.php")
     * or a file path.
     */
    public $templateFile;


    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['migrationPath', 'migrationNamespaces'], // global for all actions
            $actionID === 'create' ? ['templateFile'] : [] // action create
        );
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the [[migrationPath]].
     * @param \yii\base\Action $action the action to be executed.
     * @throws InvalidConfigException if directory specified in migrationPath doesn't exist and action isn't "create".
     * @return bool whether the action should continue to be executed.
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

            if ($this->migrationPath !== null) {
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
        } else {
            return false;
        }
    }

    /**
     * Upgrades the application by applying new migrations.
     * For example,
     *
     * ```
     * yii migrate     # apply all new migrations
     * yii migrate 3   # apply the first 3 new migrations
     * ```
     *
     * @param int $limit the number of new migrations to be applied. If 0, it means
     * applying all available new migrations.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionUp($limit = 0)
    {
        $migrations = $this->getNewMigrations();
        if (empty($migrations)) {
            $this->stdout("No new migrations found. Your system is up-to-date.\n", Console::FG_GREEN);

            return self::EXIT_CODE_NORMAL;
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
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");

        $applied = 0;
        if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateUp($migration)) {
                    $this->stdout("\n$applied from $n " . ($applied === 1 ? 'migration was' : 'migrations were') ." applied.\n", Console::FG_RED);
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return self::EXIT_CODE_ERROR;
                }
                $applied++;
            }

            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') ." applied.\n", Console::FG_GREEN);
            $this->stdout("\nMigrated up successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * Downgrades the application by reverting old migrations.
     * For example,
     *
     * ```
     * yii migrate/down     # revert the last migration
     * yii migrate/down 3   # revert the last 3 migrations
     * yii migrate/down all # revert all migrations
     * ```
     *
     * @param int $limit the number of migrations to be reverted. Defaults to 1,
     * meaning the last applied migration will be reverted.
     * @throws Exception if the number of the steps specified is less than 1.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
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

            return self::EXIT_CODE_NORMAL;
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
                    $this->stdout("\n$reverted from $n " . ($reverted === 1 ? 'migration was' : 'migrations were') ." reverted.\n", Console::FG_RED);
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return self::EXIT_CODE_ERROR;
                }
                $reverted++;
            }
            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') ." reverted.\n", Console::FG_GREEN);
            $this->stdout("\nMigrated down successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * Redoes the last few migrations.
     *
     * This command will first revert the specified migrations, and then apply
     * them again. For example,
     *
     * ```
     * yii migrate/redo     # redo the last applied migration
     * yii migrate/redo 3   # redo the last 3 applied migrations
     * yii migrate/redo all # redo all migrations
     * ```
     *
     * @param int $limit the number of migrations to be redone. Defaults to 1,
     * meaning the last applied migration will be redone.
     * @throws Exception if the number of the steps specified is less than 1.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
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

            return self::EXIT_CODE_NORMAL;
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

                    return self::EXIT_CODE_ERROR;
                }
            }
            foreach (array_reverse($migrations) as $migration) {
                if (!$this->migrateUp($migration)) {
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return self::EXIT_CODE_ERROR;
                }
            }
            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') ." redone.\n", Console::FG_GREEN);
            $this->stdout("\nMigration redone successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * Upgrades or downgrades till the specified version.
     *
     * Can also downgrade versions to the certain apply time in the past by providing
     * a UNIX timestamp or a string parseable by the strtotime() function. This means
     * that all the versions applied after the specified certain time would be reverted.
     *
     * This command will first revert the specified migrations, and then apply
     * them again. For example,
     *
     * ```
     * yii migrate/to 101129_185401                          # using timestamp
     * yii migrate/to m101129_185401_create_user_table       # using full name
     * yii migrate/to 1392853618                             # using UNIX timestamp
     * yii migrate/to "2014-02-15 13:00:50"                  # using strtotime() parseable string
     * yii migrate/to app\migrations\M101129185401CreateUser # using full namespace name
     * ```
     *
     * @param string $version either the version name or the certain time value in the past
     * that the application should be migrated to. This can be either the timestamp,
     * the full name of the migration, the UNIX timestamp, or the parseable datetime
     * string.
     * @throws Exception if the version argument is invalid.
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
     * Modifies the migration history to the specified version.
     *
     * No actual migration will be performed.
     *
     * ```
     * yii migrate/mark 101129_185401                        # using timestamp
     * yii migrate/mark m101129_185401_create_user_table     # using full name
     * yii migrate/to app\migrations\M101129185401CreateUser # using full namespace name
     * ```
     *
     * @param string $version the version at which the migration history should be marked.
     * This can be either the timestamp or the full name of the migration.
     * @return int CLI exit code
     * @throws Exception if the version argument is invalid or the version cannot be found.
     */
    public function actionMark($version)
    {
        $originalVersion = $version;
        if (($namespaceVersion = $this->extractNamespaceMigrationVersion($version)) !== false) {
            $version = $namespaceVersion;
        } elseif (($migrationName = $this->extractMigrationVersion($version)) !== false) {
            $version = $migrationName;
        } else {
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

                return self::EXIT_CODE_NORMAL;
            }
        }

        // try mark down
        $migrations = array_keys($this->getMigrationHistory(null));
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

                return self::EXIT_CODE_NORMAL;
            }
        }

        throw new Exception("Unable to find the version '$originalVersion'.");
    }

    /**
     * Checks if given migration version specification matches namespaced migration name.
     * @param string $rawVersion raw version specification received from user input.
     * @return string|false actual migration version, `false` - if not match.
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
     * Checks if given migration version specification matches migration base name.
     * @param string $rawVersion raw version specification received from user input.
     * @return string|false actual migration version, `false` - if not match.
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
     * Displays the migration history.
     *
     * This command will show the list of migrations that have been applied
     * so far. For example,
     *
     * ```
     * yii migrate/history     # showing the last 10 migrations
     * yii migrate/history 5   # showing the last 5 migrations
     * yii migrate/history all # showing the whole history
     * ```
     *
     * @param int $limit the maximum number of migrations to be displayed.
     * If it is "all", the whole migration history will be displayed.
     * @throws \yii\console\Exception if invalid limit value passed
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
     * Displays the un-applied new migrations.
     *
     * This command will show the new migrations that have not been applied.
     * For example,
     *
     * ```
     * yii migrate/new     # showing the first 10 new migrations
     * yii migrate/new 5   # showing the first 5 new migrations
     * yii migrate/new all # showing all new migrations
     * ```
     *
     * @param int $limit the maximum number of new migrations to be displayed.
     * If it is `all`, all available new migrations will be displayed.
     * @throws \yii\console\Exception if invalid limit value passed
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
     * Creates a new migration.
     *
     * This command creates a new migration using the available migration template.
     * After using this command, developers should modify the created migration
     * skeleton by filling up the actual migration logic.
     *
     * ```
     * yii migrate/create create_user_table
     * ```
     *
     * In order to generate a namespaced migration, you should specify a namespace before the migration's name.
     * Note that backslash (`\`) is usually considered a special character in the shell, so you need to escape it
     * properly to avoid shell errors or incorrect behavior.
     * For example:
     *
     * ```
     * yii migrate/create 'app\\migrations\\createUserTable'
     * ```
     *
     * In case [[migrationPath]] is not set and no namespace is provided, the first entry of [[migrationNamespaces]] will be used.
     *
     * @param string $name the name of the new migration. This should only contain
     * letters, digits, underscores and/or backslashes.
     *
     * Note: If the migration name is of a special form, for example create_xxx or
     * drop_xxx, then the generated migration file will contain extra code,
     * in this case for creating/dropping tables.
     *
     * @throws Exception if the name argument is invalid.
     */
    public function actionCreate($name)
    {
        if (!preg_match('/^[\w\\]+$/', $name)) {
            throw new Exception('The migration name should contain letters, digits, underscore and/or backslash characters only.');
        }

        list($namespace, $className) = $this->generateClassName($name);
        $migrationPath = $this->findMigrationPath($namespace);

        $file = $migrationPath . DIRECTORY_SEPARATOR . $className . '.php';
        if ($this->confirm("Create new migration '$file'?")) {
            $content = $this->generateMigrationSourceCode([
                'name' => $name,
                'className' => $className,
                'namespace' => $namespace,
            ]);
            FileHelper::createDirectory($migrationPath);
            file_put_contents($file, $content);
            $this->stdout("New migration created successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * Generates class base name and namespace from migration name from user input.
     * @param string $name migration name from user input.
     * @return array list of 2 elements: 'namespace' and 'class base name'
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
     * Finds the file path for the specified migration namespace.
     * @param string|null $namespace migration namespace.
     * @return string migration file path.
     * @throws Exception on failure.
     * @since 2.0.10
     */
    private function findMigrationPath($namespace)
    {
        if (empty($namespace)) {
            return $this->migrationPath;
        }

        if (!in_array($namespace, $this->migrationNamespaces, true)) {
            throw new Exception("Namespace '{$namespace}' not found in `migrationNamespaces`");
        }

        return $this->getNamespacePath($namespace);
    }

    /**
     * Returns the file path matching the give namespace.
     * @param string $namespace namespace.
     * @return string file path.
     * @since 2.0.10
     */
    private function getNamespacePath($namespace)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace)));
    }

    /**
     * Upgrades with the specified migration class.
     * @param string $class the migration class name
     * @return bool whether the migration is successful
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
        } else {
            $time = microtime(true) - $start;
            $this->stdout("*** failed to apply $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_RED);

            return false;
        }
    }

    /**
     * Downgrades with the specified migration class.
     * @param string $class the migration class name
     * @return bool whether the migration is successful
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
        } else {
            $time = microtime(true) - $start;
            $this->stdout("*** failed to revert $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_RED);

            return false;
        }
    }

    /**
     * Creates a new migration instance.
     * @param string $class the migration class name
     * @return \yii\db\MigrationInterface the migration instance
     */
    protected function createMigration($class)
    {
        $class = trim($class, '\\');
        if (strpos($class, '\\') === false) {
            $file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
            require_once($file);
        }

        return new $class();
    }

    /**
     * Migrates to the specified apply time in the past.
     * @param int $time UNIX timestamp value.
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
     * Migrates to the certain version.
     * @param string $version name in the full format.
     * @return int CLI exit code
     * @throws Exception if the provided version cannot be found.
     */
    protected function migrateToVersion($version)
    {
        $originalVersion = $version;

        // try migrate up
        $migrations = $this->getNewMigrations();
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version) === 0) {
                $this->actionUp($i + 1);

                return self::EXIT_CODE_NORMAL;
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

                return self::EXIT_CODE_NORMAL;
            }
        }

        throw new Exception("Unable to find the version '$originalVersion'.");
    }

    /**
     * Returns the migrations that are not applied.
     * @return array list of new migrations
     */
    protected function getNewMigrations()
    {
        $applied = [];
        foreach ($this->getMigrationHistory(null) as $class => $time) {
            $applied[trim($class, '\\')] = true;
        }

        $migrationPaths = [];
        if (!empty($this->migrationPath)) {
            $migrationPaths[''] = $this->migrationPath;
        }
        foreach ($this->migrationNamespaces as $namespace) {
            $migrationPaths[$namespace] = $this->getNamespacePath($namespace);
        }

        $migrations = [];
        foreach ($migrationPaths as $namespace => $migrationPath) {
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
     * Generates new migration source PHP code.
     * Child class may override this method, adding extra logic or variation to the process.
     * @param array $params generation parameters, usually following parameters are present:
     *
     *  - name: string migration base name
     *  - className: string migration class name
     *
     * @return string generated PHP code.
     * @since 2.0.8
     */
    protected function generateMigrationSourceCode($params)
    {
        return $this->renderFile(Yii::getAlias($this->templateFile), $params);
    }

    /**
     * Returns the migration history.
     * @param int $limit the maximum number of records in the history to be returned. `null` for "no limit".
     * @return array the migration history
     */
    abstract protected function getMigrationHistory($limit);

    /**
     * Adds new migration entry to the history.
     * @param string $version migration version name.
     */
    abstract protected function addMigrationHistory($version);

    /**
     * Removes existing migration from the history.
     * @param string $version migration version name.
     */
    abstract protected function removeMigrationHistory($version);
}
