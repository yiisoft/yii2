<?php
/**
 * MigrateController class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Exception;
use yii\console\Controller;
use yii\db\Connection;

/**
 * This command provides support for database migrations.
 *
 * EXAMPLES
 *
 * - yiic migrate
 *   Applies ALL new migrations. This is equivalent to 'yiic migrate up'.
 *
 * - yiic migrate create create_user_table
 *   Creates a new migration named 'create_user_table'.
 *
 * - yiic migrate up 3
 *   Applies the next 3 new migrations.
 *
 * - yiic migrate down
 *   Reverts the last applied migration.
 *
 * - yiic migrate down 3
 *   Reverts the last 3 applied migrations.
 *
 * - yiic migrate to 101129_185401
 *   Migrates up or down to version 101129_185401.
 *
 * - yiic migrate mark 101129_185401
 *   Modifies the migration history up or down to version 101129_185401.
 *   No actual migration will be performed.
 *
 * - yiic migrate history
 *   Shows all previously applied migration information.
 *
 * - yiic migrate history 10
 *   Shows the last 10 applied migrations.
 *
 * - yiic migrate new
 *   Shows all new migrations.
 *
 * - yiic migrate new 10
 *   Shows the next 10 migrations that have not been applied.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MigrateController extends Controller
{
	/**
	 * The name of the dummy migration that marks the beginning of the whole migration history.
	 */
	const BASE_MIGRATION = 'm000000_000000_base';

	/**
	 * @var string the directory that stores the migrations. This can be either a path alias
	 * or a directory. Defaults to '@application/migrations'.
	 */
	public $migrationPath = '@application/migrations';
	/**
	 * @var string the name of the table for keeping applied migration information.
	 * This table will be automatically created if not exists.
	 * The table structure is as follows:
	 *
	 * ~~~
	 * CREATE TABLE tbl_migration (
	 *     version varchar(255) PRIMARY KEY,
	 *     apply_time integer
	 * )
	 * ~~~
	 */
	public $migrationTable = 'tbl_migration';
	/**
	 * @var string the component ID that specifies the database connection for
	 * storing migration information.
	 */
	public $connectionID = 'db';
	/**
	 * @var string the path of the template file for generating new migrations.
	 * This can be either a path alias (e.g. "@application/migrations/template.php")
	 * or a file path. If not set, an internal template will be used.
	 */
	public $templateFile;
	/**
	 * @var string the default command action.
	 */
	public $defaultAction = 'up';
	/**
	 * @var boolean whether to execute the migration in an interactive mode.
	 */
	public $interactive = true;

	public function globalOptions()
	{
		return array('migrationPath', 'migrationTable', 'connectionID', 'templateFile', 'interactive');
	}
	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * It checks the existence of the [[migrationPath]].
	 * @param \yii\base\Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 * @throws Exception if the migration directory does not exist.
	 */
	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {
			$path = Yii::getAlias($this->migrationPath);
			if ($path === false || !is_dir($path)) {
				throw new Exception("The migration directory \"{$this->migrationPath}\" does not exist.");
			}
			$this->migrationPath = $path;
			$version = Yii::getVersion();
			echo "\nYii Migration Tool (based on Yii v{$version})\n\n";
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Upgrades the application by applying new migrations. For example,
	 *
	 * ~~~
	 * yiic migrate     # apply all new migrations
	 * yiic migrate 3   # apply the first 3 new migrations
	 * ~~~
	 *
	 * @param array $args the number of new migrations to be applied. If not provided,
	 * all new migrations will be applied.
	 */
	public function actionUp($args)
	{
		if (($migrations = $this->getNewMigrations()) === array()) {
			echo "No new migration found. Your system is up-to-date.\n";
			Yii::$application->end();
		}

		$total = count($migrations);
		$step = isset($args[0]) ? (int)$args[0] : 0;
		if ($step > 0) {
			$migrations = array_slice($migrations, 0, $step);
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
				if ($this->migrateUp($migration) === false) {
					echo "\nMigration failed. The rest of the new migrations are canceled.\n";
					return;
				}
			}
			echo "\nMigrated up successfully.\n";
		}
	}

	/**
	 * Downgrades the application by reverting old migrations. For example,
	 *
	 * ~~~
	 * yiic migrate/down     # revert the last migration
	 * yiic migrate/down 3   # revert the last 3 migrations
	 * ~~~
	 *
	 * @param array $args the number of migrations to be reverted. If not provided,
	 * the last applied migration will be reverted.
	 * @throws Exception if the number of the steps is less than 1.
	 */
	public function actionDown($args)
	{
		$step = isset($args[0]) ? (int)$args[0] : 1;
		if ($step < 1) {
			throw new Exception("The step parameter must be greater than 0.");
		}

		if (($migrations = $this->getMigrationHistory($step)) === array()) {
			echo "No migration has been done before.\n";
			return;
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
				if ($this->migrateDown($migration) === false) {
					echo "\nMigration failed. All later migrations are canceled.\n";
					return;
				}
			}
			echo "\nMigrated down successfully.\n";
		}
	}

	public function actionRedo($args)
	{
		$step = isset($args[0]) ? (int)$args[0] : 1;
		if ($step < 1) {
			die("Error: The step parameter must be greater than 0.\n");
		}

		if (($migrations = $this->getMigrationHistory($step)) === array()) {
			echo "No migration has been done before.\n";
			return;
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
				if ($this->migrateDown($migration) === false) {
					echo "\nMigration failed. All later migrations are canceled.\n";
					return;
				}
			}
			foreach (array_reverse($migrations) as $migration) {
				if ($this->migrateUp($migration) === false) {
					echo "\nMigration failed. All later migrations are canceled.\n";
					return;
				}
			}
			echo "\nMigration redone successfully.\n";
		}
	}

	public function actionTo($args)
	{
		if (isset($args[0])) {
			$version = $args[0];
		} else {
			throw new Exception("Please specify which version to migrate to.");
		}

		$originalVersion = $version;
		if (preg_match('/^m?(\d{6}_\d{6})(_.*?)?$/', $version, $matches)) {
			$version = 'm' . $matches[1];
		} else {
			throw new Exception("The version option must be either a timestamp (e.g. 101129_185401)\nor the full name of a migration (e.g. m101129_185401_create_user_table).");
		}

		// try migrate up
		$migrations = $this->getNewMigrations();
		foreach ($migrations as $i => $migration) {
			if (strpos($migration, $version . '_') === 0) {
				$this->actionUp(array($i + 1));
				return;
			}
		}

		// try migrate down
		$migrations = array_keys($this->getMigrationHistory(-1));
		foreach ($migrations as $i => $migration) {
			if (strpos($migration, $version . '_') === 0) {
				if ($i === 0) {
					echo "Already at '$originalVersion'. Nothing needs to be done.\n";
				} else {
					$this->actionDown(array($i));
				}
				return;
			}
		}

		throw new Exception("Unable to find the version '$originalVersion'.");
	}

	public function actionMark($args)
	{
		if (isset($args[0])) {
			$version = $args[0];
		} else {
			throw new Exception('Please specify which version to mark to.');
		}
		$originalVersion = $version;
		if (preg_match('/^m?(\d{6}_\d{6})(_.*?)?$/', $version, $matches)) {
			$version = 'm' . $matches[1];
		} else {
			throw new Exception("Error: The version option must be either a timestamp (e.g. 101129_185401)\nor the full name of a migration (e.g. m101129_185401_create_user_table).");
		}

		$db = $this->getDb();

		// try mark up
		$migrations = $this->getNewMigrations();
		foreach ($migrations as $i => $migration) {
			if (strpos($migration, $version . '_') === 0) {
				if ($this->confirm("Set migration history at $originalVersion?")) {
					$command = $db->createCommand();
					for ($j = 0; $j <= $i; ++$j) {
						$command->insert($this->migrationTable, array(
							'version' => $migrations[$j],
							'apply_time' => time(),
						));
					}
					echo "The migration history is set at $originalVersion.\nNo actual migration was performed.\n";
				}
				return;
			}
		}

		// try mark down
		$migrations = array_keys($this->getMigrationHistory(-1));
		foreach ($migrations as $i => $migration) {
			if (strpos($migration, $version . '_') === 0) {
				if ($i === 0) {
					echo "Already at '$originalVersion'. Nothing needs to be done.\n";
				} else {
					if ($this->confirm("Set migration history at $originalVersion?")) {
						$command = $db->createCommand();
						for ($j = 0; $j < $i; ++$j) {
							$command->delete($this->migrationTable, $db->quoteColumnName('version') . '=:version', array(':version' => $migrations[$j]));
						}
						echo "The migration history is set at $originalVersion.\nNo actual migration was performed.\n";
					}
				}
				return;
			}
		}

		die("Error: Unable to find the version '$originalVersion'.\n");
	}

	public function actionHistory($args)
	{
		$limit = isset($args[0]) ? (int)$args[0] : -1;
		$migrations = $this->getMigrationHistory($limit);
		if ($migrations === array()) {
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

	public function actionNew($args)
	{
		$limit = isset($args[0]) ? (int)$args[0] : -1;
		$migrations = $this->getNewMigrations();
		if ($migrations === array()) {
			echo "No new migrations found. Your system is up-to-date.\n";
		} else {
			$n = count($migrations);
			if ($limit > 0 && $n > $limit) {
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
	 * @param array $args the name of the new migration.
	 * @throws Exception if the name of the new migration is not provided
	 */
	public function actionCreate($args)
	{
		if (isset($args[0])) {
			$name = $args[0];
		} else {
			throw new Exception('Please provide the name of the new migration.');
		}

		if (!preg_match('/^\w+$/', $name)) {
			die("Error: The name of the migration must contain letters, digits and/or underscore characters only.\n");
		}

		$name = 'm' . gmdate('ymd_His') . '_' . $name;
		$content = strtr($this->getTemplate(), array('{ClassName}' => $name));
		$file = $this->migrationPath . DIRECTORY_SEPARATOR . $name . '.php';

		if ($this->confirm("Create new migration '$file'?")) {
			file_put_contents($file, $content);
			echo "New migration created successfully.\n";
		}
	}

	protected function migrateUp($class)
	{
		if ($class === self::BASE_MIGRATION) {
			return;
		}

		echo "*** applying $class\n";
		$start = microtime(true);
		$migration = $this->instantiateMigration($class);
		if ($migration->up() !== false) {
			$this->getDb()->createCommand()->insert($this->migrationTable, array(
				'version' => $class,
				'apply_time' => time(),
			));
			$time = microtime(true) - $start;
			echo "*** applied $class (time: " . sprintf("%.3f", $time) . "s)\n\n";
		} else {
			$time = microtime(true) - $start;
			echo "*** failed to apply $class (time: " . sprintf("%.3f", $time) . "s)\n\n";
			return false;
		}
	}

	protected function migrateDown($class)
	{
		if ($class === self::BASE_MIGRATION) {
			return;
		}

		echo "*** reverting $class\n";
		$start = microtime(true);
		$migration = $this->instantiateMigration($class);
		if ($migration->down() !== false) {
			$db = $this->getDb();
			$db->createCommand()->delete($this->migrationTable, $db->quoteColumnName('version') . '=:version', array(':version' => $class));
			$time = microtime(true) - $start;
			echo "*** reverted $class (time: " . sprintf("%.3f", $time) . "s)\n\n";
		} else {
			$time = microtime(true) - $start;
			echo "*** failed to revert $class (time: " . sprintf("%.3f", $time) . "s)\n\n";
			return false;
		}
	}

	protected function instantiateMigration($class)
	{
		$file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
		require_once($file);
		$migration = new $class;
		$migration->db = $this->getDb();
		return $migration;
	}

	/**
	 * @var CDbConnection
	 */
	private $_db;

	protected function getDb()
	{
		if ($this->_db !== null) {
			return $this->_db;
		} else {
			if (($this->_db = Yii::$application->getComponent($this->connectionID)) instanceof CDbConnection) {
				return $this->_db;
			} else {
				throw new Exception("Invalid DB connection: {$this->connectionID}.");
			}
		}
	}

	protected function getMigrationHistory($limit)
	{
		$db = $this->getDb();
		if ($db->schema->getTable($this->migrationTable) === null) {
			$this->createMigrationHistoryTable();
		}
		return CHtml::listData($db->createCommand()
			->select('version, apply_time')
			->from($this->migrationTable)
			->order('version DESC')
			->limit($limit)
			->queryAll(), 'version', 'apply_time');
	}

	protected function createMigrationHistoryTable()
	{
		$db = $this->getDb();
		echo 'Creating migration history table "' . $this->migrationTable . '"...';
		$db->createCommand()->createTable($this->migrationTable, array(
			'version' => 'string NOT NULL PRIMARY KEY',
			'apply_time' => 'integer',
		));
		$db->createCommand()->insert($this->migrationTable, array(
			'version' => self::BASE_MIGRATION,
			'apply_time' => time(),
		));
		echo "done.\n";
	}

	protected function getNewMigrations()
	{
		$applied = array();
		foreach ($this->getMigrationHistory(-1) as $version => $time) {
			$applied[substr($version, 1, 13)] = true;
		}

		$migrations = array();
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

	protected function getTemplate()
	{
		if ($this->templateFile !== null) {
			return file_get_contents(Yii::getAlias($this->templateFile));
		} else {
			return <<<EOD
<?php

class {ClassName} extends CDbMigration
{
	public function up()
	{
	}

	public function down()
	{
		echo "{ClassName} does not support migration down.\\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
EOD;
		}
	}
}
