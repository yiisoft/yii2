<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\Exception;

/**
 * This command manages fixtures load to the database tables.
 * You can specify different options of this command to point fixture manager
 * to the specific tables of the different database connections.
 *
 * To use this command simply configure your console.php config like this:
 *
 * ~~~
 * 'db' => [
 *     'class' => 'yii\db\Connection',
 *     'dsn' => 'mysql:host=localhost;dbname={your_database}',
 *     'username' => '{your_db_user}',
 *     'password' => '',
 *     'charset' => 'utf8',
 * ],
 * 'fixture' => [
 *     'class' => 'yii\test\DbFixtureManager',
 * ],
 * ~~~
 *
 * ~~~
 * #load fixtures under $fixturePath to the "users" table
 * yii fixture/apply users
 *
 * #also a short version of this command (generate action is default)
 * yii fixture users
 *
 * #load fixtures under $fixturePath to the "users" table to the different connection
 * yii fixture/apply users --db=someOtherDbConneciton
 *
 * #load fixtures under different $fixturePath to the "users" table.
 * yii fixture/apply users --fixturePath=@app/some/other/path/to/fixtures
 * ~~~
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class FixtureController extends Controller
{

	use \yii\test\DbTestTrait;

	/**
	 * @var string controller default action ID.
	 */
	public $defaultAction = 'apply';

	/**
	 * Alias to the path, where all fixtures are stored.
	 * @var string
	 */
	public $fixturePath = '@tests/unit/fixtures';

	/**
	 * Id of the database connection component of the application.
	 * @var string
	 */
	public $db = 'db';

	/**
	 * Returns the names of the global options for this command.
	 * @return array the names of the global options for this command.
	 */
	public function globalOptions()
	{
		return array_merge(parent::globalOptions(), [
			'db', 'fixturePath'
		]);
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * It checks that fixtures path and database connection are available.
	 * @param \yii\base\Action $action
	 * @return boolean
	 */
	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {
			$this->checkRequirements();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Apply given fixture to the table. Fixture name can be the same as the table name or
	 * you can specify table name as a second parameter.
	 * @param string $fixture
	 */
	public function actionApply($fixture)
	{
		$this->fixtureManager->basePath = $this->fixturePath;
		$this->fixtureManager->db = $this->db;
		$this->loadFixtures([$fixture]);
	}

	/**
	 * Truncate given table and clear all fixtures from it.
	 * @param string $table
	 */
	public function actionClear($table)
	{
		$this->getDbConnection()->createCommand()->truncateTable($table)->execute();
		echo "Table \"{$table}\" was successfully cleared. \n";
	}

	/**
	 * Checks if the database and fixtures path are available.
	 * @throws Exception
	 */
	public function checkRequirements()
	{
		$path = Yii::getAlias($this->fixturePath, false);

		if (!is_dir($path) || !is_writable($path)) {
			throw new Exception("The fixtures path \"{$this->fixturePath}\" not exist or is not writable");
		}

	}

	/**
	 * Returns database connection component
	 * @return \yii\db\Connection|null
	 */
	public function getDbConnection()
	{
		$db = Yii::$app->getComponent($this->db);

		if ($db == null) {
			throw new Exception("There is no database connection component with id \"{$this->db}\".");
		}

		return $db;
	}

}
