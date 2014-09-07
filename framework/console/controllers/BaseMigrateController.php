<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Exception;
use yii\console\Controller;
use yii\helpers\FileHelper;

/**
 * BaseMigrateController is base class for migrate controllers.
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
     * @var string the directory storing the migration classes. This can be either
     * a path alias or a directory.
     */
    public $migrationPath = '@app/migrations';
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
            ['migrationPath'], // global for all actions
            ($actionID == 'create') ? ['templateFile'] : [] // action create
        );
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the [[migrationPath]].
     * @param \yii\base\Action $action the action to be executed.
     * @throws Exception if db component isn't configured
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $path = Yii::getAlias($this->migrationPath);
            if (!is_dir($path)) {
                echo "";
                FileHelper::createDirectory($path);
            }
            $this->migrationPath = $path;

            $version = Yii::getVersion();
            echo "Yii Migration Tool (based on Yii v{$version})\n\n";

            return true;
        } else {
            return false;
        }
    }

    /**
     * Upgrades the application by applying new migrations.
     * For example,
     *
     * ~~~
     * yii migrate     # apply all new migrations
     * yii migrate 3   # apply the first 3 new migrations
     * ~~~
     *
     * @param integer $limit the number of new migrations to be applied. If 0, it means
     * applying all available new migrations.
     *
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionUp($limit = 0)
    {
        $migrations = $this->getNewMigrations();
        if (empty($migrations)) {
            echo "No new migration found. Your system is up-to-date.\n";

            return self::EXIT_CODE_NORMAL;
        }

        $total = count($migrations);
        $limit = (int) $limit;
        if ($limit > 0) {
            $migrations = array_slice($migrations, 0, $limit);
        }

        $n = count($migrations);
        if ($n === $total) {
            echo "Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:\n";
        } else {
            echo "Total $n out of $total new " . ($total === 1 ? 'migration' : 'migrations') . " to be applied:\n";
        }

        foreach ($migrations as $migration) {
            echo "    $migration\n";
        }
        echo "\n";

        if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . "?")) {
            foreach ($migrations as $migration) {
                if (!$this->migrateUp($migration)) {
                    echo "\nMigration failed. The rest of the migrations are canceled.\n";

                    return self::EXIT_CODE_ERROR;
                }
            }
            echo "\nMigrated up successfully.\n";
        }
    }

    /**
     * Downgrades the application by reverting old migrations.
     * For example,
     *
     * ~~~
     * yii migrate/down     # revert the last migration
     * yii migrate/down 3   # revert the last 3 migrations
     * yii migrate/down all # revert all migrations
     * ~~~
     *
     * @param integer $limit the number of migrations to be reverted. Defaults to 1,
     * meaning the last applied migration will be reverted.
     * @throws Exception if the number of the steps specified is less than 1.
     *
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionDown($limit = 1)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int) $limit;
            if ($limit < 1) {
                throw new Exception("The step argument must be greater than 0.");
            }
        }

        $migrations = $this->getMigrationHistory($limit);

        if (empty($migrations)) {
            echo "No migration has been done before.\n";

            return self::EXIT_CODE_NORMAL;
        }

        $migrations = array_keys($migrations);

        $n = count($migrations);
        echo "Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be reverted:\n";
        foreach ($migrations as $migration) {
            echo "    $migration\n";
        }
        echo "\n";

        if ($this->confirm('Revert the above ' . ($n === 1 ? 'migration' : 'migrations') . "?")) {
            foreach ($migrations as $migration) {
                if (!$this->migrateDown($migration)) {
                    echo "\nMigration failed. The rest of the migrations are canceled.\n";

                    return self::EXIT_CODE_ERROR;
                }
            }
            echo "\nMigrated down successfully.\n";
        }
    }

    /**
     * Redoes the last few migrations.
     *
     * This command will first revert the specified migrations, and then apply
     * them again. For example,
     *
     * ~~~
     * yii migrate/redo     # redo the last applied migration
     * yii migrate/redo 3   # redo the last 3 applied migrations
     * yii migrate/redo all # redo all migrations
     * ~~~
     *
     * @param integer $limit the number of migrations to be redone. Defaults to 1,
     * meaning the last applied migration will be redone.
     * @throws Exception if the number of the steps specified is less than 1.
     *
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionRedo($limit = 1)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int) $limit;
            if ($limit < 1) {
                throw new Exception("The step argument must be greater than 0.");
            }
        }

        $migrations = $this->getMigrationHistory($limit);

        if (empty($migrations)) {
            echo "No migration has been done before.\n";

            return self::EXIT_CODE_NORMAL;
        }

        $migrations = array_keys($migrations);

        $n = count($migrations);
        echo "Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be redone:\n";
        foreach ($migrations as $migration) {
            echo "    $migration\n";
        }
        echo "\n";

        if ($this->confirm('Redo the above ' . ($n === 1 ? 'migration' : 'migrations') . "?")) {
            foreach ($migrations as $migration) {
                if (!$this->migrateDown($migration)) {
                    echo "\nMigration failed. The rest of the migrations are canceled.\n";

                    return self::EXIT_CODE_ERROR;
                }
            }
            foreach (array_reverse($migrations) as $migration) {
                if (!$this->migrateUp($migration)) {
                    echo "\nMigration failed. The rest of the migrations migrations are canceled.\n";

                    return self::EXIT_CODE_ERROR;
                }
            }
            echo "\nMigration redone successfully.\n";
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
     * ~~~
     * yii migrate/to 101129_185401                      # using timestamp
     * yii migrate/to m101129_185401_create_user_table   # using full name
     * yii migrate/to 1392853618                         # using UNIX timestamp
     * yii migrate/to "2014-02-15 13:00:50"              # using strtotime() parseable string
     * ~~~
     *
     * @param string $version either the version name or the certain time value in the past
     * that the application should be migrated to. This can be either the timestamp,
     * the full name of the migration, the UNIX timestamp, or the parseable datetime
     * string.
     * @throws Exception if the version argument is invalid.
     */
    public function actionTo($version)
    {
        if (preg_match('/^m?(\d{6}_\d{6})(_.*?)?$/', $version, $matches)) {
            $this->migrateToVersion('m' . $matches[1]);
        } elseif ((string) (int) $version == $version) {
            $this->migrateToTime($version);
        } elseif (($time = strtotime($version)) !== false) {
            $this->migrateToTime($time);
        } else {
            throw new Exception("The version argument must be either a timestamp (e.g. 101129_185401),\n the full name of a migration (e.g. m101129_185401_create_user_table),\n a UNIX timestamp (e.g. 1392853000), or a datetime string parseable\nby the strtotime() function (e.g. 2014-02-15 13:00:50).");
        }
    }

    /**
     * Modifies the migration history to the specified version.
     *
     * No actual migration will be performed.
     *
     * ~~~
     * yii migrate/mark 101129_185401                      # using timestamp
     * yii migrate/mark m101129_185401_create_user_table   # using full name
     * ~~~
     *
     * @param string $version the version at which the migration history should be marked.
     * This can be either the timestamp or the full name of the migration.
     * @throws Exception if the version argument is invalid or the version cannot be found.
     */
    public function actionMark($version)
    {
        $originalVersion = $version;
        if (preg_match('/^m?(\d{6}_\d{6})(_.*?)?$/', $version, $matches)) {
            $version = 'm' . $matches[1];
        } else {
            throw new Exception("The version argument must be either a timestamp (e.g. 101129_185401)\nor the full name of a migration (e.g. m101129_185401_create_user_table).");
        }

        // try mark up
        $migrations = $this->getNewMigrations();
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version . '_') === 0) {
                if ($this->confirm("Set migration history at $originalVersion?")) {
                    for ($j = 0; $j <= $i; ++$j) {
                        $this->addMigrationHistory($migrations[$j]);
                    }
                    echo "The migration history is set at $originalVersion.\nNo actual migration was performed.\n";
                }

                return self::EXIT_CODE_NORMAL;
            }
        }

        // try mark down
        $migrations = array_keys($this->getMigrationHistory(null));
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version . '_') === 0) {
                if ($i === 0) {
                    echo "Already at '$originalVersion'. Nothing needs to be done.\n";
                } else {
                    if ($this->confirm("Set migration history at $originalVersion?")) {
                        for ($j = 0; $j < $i; ++$j) {
                            $this->removeMigrationHistory($migrations[$j]);
                        }
                        echo "The migration history is set at $originalVersion.\nNo actual migration was performed.\n";
                    }
                }

                return self::EXIT_CODE_NORMAL;
            }
        }

        throw new Exception("Unable to find the version '$originalVersion'.");
    }

    /**
     * Displays the migration history.
     *
     * This command will show the list of migrations that have been applied
     * so far. For example,
     *
     * ~~~
     * yii migrate/history     # showing the last 10 migrations
     * yii migrate/history 5   # showing the last 5 migrations
     * yii migrate/history all # showing the whole history
     * ~~~
     *
     * @param integer $limit the maximum number of migrations to be displayed.
     * If it is 0, the whole migration history will be displayed.
     * @throws \yii\console\Exception if invalid limit value passed
     */
    public function actionHistory($limit = 10)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int) $limit;
            if ($limit < 1) {
                throw new Exception("The limit must be greater than 0.");
            }
        }

        $migrations = $this->getMigrationHistory($limit);

        if (empty($migrations)) {
            echo "No migration has been done before.\n";
        } else {
            $n = count($migrations);
            if ($limit > 0) {
                echo "Showing the last $n applied " . ($n === 1 ? 'migration' : 'migrations') . ":\n";
            } else {
                echo "Total $n " . ($n === 1 ? 'migration has' : 'migrations have') . " been applied before:\n";
            }
            foreach ($migrations as $version => $time) {
                echo "    (" . date('Y-m-d H:i:s', $time) . ') ' . $version . "\n";
            }
        }
    }

    /**
     * Displays the un-applied new migrations.
     *
     * This command will show the new migrations that have not been applied.
     * For example,
     *
     * ~~~
     * yii migrate/new     # showing the first 10 new migrations
     * yii migrate/new 5   # showing the first 5 new migrations
     * yii migrate/new all # showing all new migrations
     * ~~~
     *
     * @param integer $limit the maximum number of new migrations to be displayed.
     * If it is 0, all available new migrations will be displayed.
     * @throws \yii\console\Exception if invalid limit value passed
     */
    public function actionNew($limit = 10)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int) $limit;
            if ($limit < 1) {
                throw new Exception("The limit must be greater than 0.");
            }
        }

        $migrations = $this->getNewMigrations();

        if (empty($migrations)) {
            echo "No new migrations found. Your system is up-to-date.\n";
        } else {
            $n = count($migrations);
            if ($limit && $n > $limit) {
                $migrations = array_slice($migrations, 0, $limit);
                echo "Showing $limit out of $n new " . ($n === 1 ? 'migration' : 'migrations') . ":\n";
            } else {
                echo "Found $n new " . ($n === 1 ? 'migration' : 'migrations') . ":\n";
            }

            foreach ($migrations as $migration) {
                echo "    " . $migration . "\n";
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
     * ~~~
     * yii migrate/create create_user_table
     * ~~~
     *
     * @param string $name the name of the new migration. This should only contain
     * letters, digits and/or underscores.
     * @throws Exception if the name argument is invalid.
     */
    public function actionCreate($name)
    {
        if (!preg_match('/^\w+$/', $name)) {
            throw new Exception("The migration name should contain letters, digits and/or underscore characters only.");
        }

        $name = 'm' . gmdate('ymd_His') . '_' . $name;
        $file = $this->migrationPath . DIRECTORY_SEPARATOR . $name . '.php';

        if ($this->confirm("Create new migration '$file'?")) {
            $content = $this->renderFile(Yii::getAlias($this->templateFile), ['className' => $name]);
            file_put_contents($file, $content);
            echo "New migration created successfully.\n";
        }
    }

    /**
     * Upgrades with the specified migration class.
     * @param string $class the migration class name
     * @return boolean whether the migration is successful
     */
    protected function migrateUp($class)
    {
        if ($class === self::BASE_MIGRATION) {
            return true;
        }

        echo "*** applying $class\n";
        $start = microtime(true);
        $migration = $this->createMigration($class);
        if ($migration->up() !== false) {
            $this->addMigrationHistory($class);
            $time = microtime(true) - $start;
            echo "*** applied $class (time: " . sprintf("%.3f", $time) . "s)\n\n";

            return true;
        } else {
            $time = microtime(true) - $start;
            echo "*** failed to apply $class (time: " . sprintf("%.3f", $time) . "s)\n\n";

            return false;
        }
    }

    /**
     * Downgrades with the specified migration class.
     * @param string $class the migration class name
     * @return boolean whether the migration is successful
     */
    protected function migrateDown($class)
    {
        if ($class === self::BASE_MIGRATION) {
            return true;
        }

        echo "*** reverting $class\n";
        $start = microtime(true);
        $migration = $this->createMigration($class);
        if ($migration->down() !== false) {
            $this->removeMigrationHistory($class);
            $time = microtime(true) - $start;
            echo "*** reverted $class (time: " . sprintf("%.3f", $time) . "s)\n\n";

            return true;
        } else {
            $time = microtime(true) - $start;
            echo "*** failed to revert $class (time: " . sprintf("%.3f", $time) . "s)\n\n";

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
        $file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
        require_once($file);

        return new $class();
    }

    /**
     * Migrates to the specified apply time in the past.
     * @param integer $time UNIX timestamp value.
     */
    protected function migrateToTime($time)
    {
        $count = 0;
        $migrations = array_values($this->getMigrationHistory(null));
        while ($count < count($migrations) && $migrations[$count] > $time) {
            ++$count;
        }
        if ($count === 0) {
            echo "Nothing needs to be done.\n";
        } else {
            $this->actionDown($count);
        }
    }

    /**
     * Migrates to the certain version.
     * @param string $version name in the full format.
     * @throws Exception if the provided version cannot be found.
     */
    protected function migrateToVersion($version)
    {
        $originalVersion = $version;

        // try migrate up
        $migrations = $this->getNewMigrations();
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version . '_') === 0) {
                $this->actionUp($i + 1);

                return self::EXIT_CODE_NORMAL;
            }
        }

        // try migrate down
        $migrations = array_keys($this->getMigrationHistory(null));
        foreach ($migrations as $i => $migration) {
            if (strpos($migration, $version . '_') === 0) {
                if ($i === 0) {
                    echo "Already at '$originalVersion'. Nothing needs to be done.\n";
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
        foreach ($this->getMigrationHistory(null) as $version => $time) {
            $applied[substr($version, 1, 13)] = true;
        }

        $migrations = [];
        $handle = opendir($this->migrationPath);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $this->migrationPath . DIRECTORY_SEPARATOR . $file;
            if (preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/', $file, $matches) && is_file($path) && !isset($applied[$matches[2]])) {
                $migrations[] = $matches[1];
            }
        }
        closedir($handle);
        sort($migrations);

        return $migrations;
    }

    /**
     * Returns the migration history.
     * @param integer $limit the maximum number of records in the history to be returned. `null` for "no limit".
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