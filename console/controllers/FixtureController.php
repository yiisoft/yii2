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
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\test\FixtureTrait;

/**
 * This command manages loading and unloading fixtures.
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
 * ~~~
 *
 * ~~~
 * #load fixtures under $fixturePath from UsersFixture class with default namespace "tests\unit\fixtures"
 * yii fixture/apply User
 *
 * #also a short version of this command (generate action is default)
 * yii fixture User
 *
 * #load fixtures under $fixturePath with the different database connection
 * yii fixture/apply User --db=someOtherDbConnection
 *
 * #load fixtures under different $fixturePath.
 * yii fixture/apply User --namespace=alias\my\custom\namespace\goes\here
 * ~~~
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class FixtureController extends Controller
{

	use FixtureTrait;

	/**
	 * type of fixture apply to database
	 */
	const APPLY_ALL = 'all';

	/**
	 * @var string controller default action ID.
	 */
	public $defaultAction = 'apply';
	/**
	 * @var string id of the database connection component of the application.
	 */
	public $db = 'db';

	/**
	 * @var string default namespace to search fixtures in
	 */
	public $namespace = 'tests\unit\fixtures';

	/**
	 * Returns the names of the global options for this command.
	 * @return array the names of the global options for this command.
	 */
	public function globalOptions()
	{
		return array_merge(parent::globalOptions(), [
			'db', 'namespace'
		]);
	}

	/**
	 * Apply given fixture to the table. You can load several fixtures specifying
	 * their names separated with commas, like: tbl_user,tbl_profile. Be sure there is no
	 * whitespace between tables names.
	 * @param array $fixtures
	 * @throws \yii\console\Exception
	 */
	public function actionApply(array $fixtures, array $except = [])
	{
		$foundFixtures = $this->findFixtures($fixtures);

		if (!$this->needToApplyAll($fixtures[0])) {
			$notFoundFixtures = array_diff($fixtures, $foundFixtures);

			if ($notFoundFixtures) {
				$this->notifyNotFound($notFoundFixtures);
			}
		}

		if (!$foundFixtures) {
			throw new Exception("No files were found by name: \"" . implode(', ', $fixtures) . "\".\n"
				. "Check that files with these name exists, under fixtures path: \n\"" . Yii::getAlias($this->getFixturePath()) . "\"."
			);
		}

		if (!$this->confirmApply($foundFixtures, $except)) {
			return;
		}

		$fixtures = $this->getFixturesConfig(array_diff($foundFixtures, $except));

		if (!$fixtures) {
			throw new Exception('No fixtures were found in namespace: "' . $this->namespace . '"' . '');
		}

		$transaction = Yii::$app->db->beginTransaction();

		try {
			$this->getDbConnection()->createCommand()->checkIntegrity(false)->execute();
			$this->loadFixtures($fixtures);
			$this->getDbConnection()->createCommand()->checkIntegrity(true)->execute();
			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollback();
			$this->stdout("Exception occurred, transaction rollback. Tables will be in same state.\n", Console::BG_RED);
			throw $e;
		}
		$this->notifySuccess($foundFixtures);
	}

	/**
	 * Unloads given fixtures. You can clear environment and unload multiple fixtures by specifying
	 * their names separated with commas, like: tbl_user,tbl_profile. Be sure there is no
	 * whitespace between tables names.
	 * @param array|string $fixtures
	 * @param array|string $except
	 */
	public function actionClear(array $fixtures, array $except = [])
	{
		$foundFixtures = $this->findFixtures($fixtures);

		if (!$this->needToApplyAll($fixtures[0])) {
			$notFoundFixtures = array_diff($fixtures, $foundFixtures);

			if ($notFoundFixtures) {
				$this->notifyNotFound($notFoundFixtures);
			}
		}

		if (!$foundFixtures) {
			throw new Exception("No files were found by name: \"" . implode(', ', $fixtures) . "\".\n"
				. "Check that fixtures with these name exists, under fixtures path: \n\"" . Yii::getAlias($this->getFixturePath()) . "\"."
			);
		}

		if (!$this->confirmClear($foundFixtures, $except)) {
			return;
		}

		$fixtures = $this->getFixturesConfig(array_diff($foundFixtures, $except));

		if (!$fixtures) {
			throw new Exception('No fixtures were found in namespace: ' . $this->namespace . '".');
		}

		$transaction = Yii::$app->db->beginTransaction();

		try {
			$this->getDbConnection()->createCommand()->checkIntegrity(false)->execute();

			foreach ($fixtures as $fixtureConfig) {
				$fixture = Yii::createObject($fixtureConfig);
				$fixture->unload();
				$this->stdout("\tFixture \"{$fixture::className()}\" was successfully unloaded. \n", Console::FG_GREEN);
			}

			$this->getDbConnection()->createCommand()->checkIntegrity(true)->execute();
			$transaction->commit();

		} catch (\Exception $e) {
			$transaction->rollback();
			$this->stdout("Exception occurred, transaction rollback. Tables will be in same state.\n", Console::BG_RED);
			throw $e;
		}
	}

	/**
	 * Returns database connection component
	 * @return \yii\db\Connection
	 * @throws \yii\console\Exception if [[db]] is invalid.
	 */
	public function getDbConnection()
	{
		$db = Yii::$app->getComponent($this->db);

		if ($db === null) {
			throw new Exception("There is no database connection component with id \"{$this->db}\".");
		}

		return $db;
	}

	/**
	 * Notifies user that fixtures were successfully loaded.
	 * @param array $fixtures
	 */
	private function notifySuccess($fixtures)
	{
		$this->stdout("Fixtures were successfully loaded from namespace:\n", Console::FG_YELLOW);
		$this->stdout("\t\"" . Yii::getAlias($this->namespace) . "\"\n\n", Console::FG_GREEN);
		$this->outputList($fixtures);
	}

	/**
	 * Notifies user that fixtures were not found under fixtures path.
	 * @param array $fixtures
	 */
	private function notifyNotFound($fixtures)
	{
		$this->stdout("Some fixtures were not found under path:\n", Console::BG_RED);
		$this->stdout("\t" . Yii::getAlias($this->getFixturePath()) . "\n\n", Console::FG_GREEN);
		$this->stdout("Check that they have correct namespace \"{$this->namespace}\" \n", Console::BG_RED);
		$this->outputList($fixtures);
		$this->stdout("\n");
	}

	/**
	 * Prompts user with confirmation if fixtures should be loaded.
	 * @param array $fixtures
	 * @param array $except
	 * @return boolean
	 */
	private function confirmApply($fixtures, $except)
	{
		$this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
		$this->stdout("\t" . $this->namespace . "\n\n", Console::FG_GREEN);

		$this->stdout("Fixtures below will be loaded:\n\n", Console::FG_YELLOW);
		$this->outputList($fixtures);

		if (count($except)) {
			$this->stdout("\nFixtures that will NOT be loaded: \n\n", Console::FG_YELLOW);
			$this->outputList($except);
		}

		return $this->confirm("\nLoad above fixtures?");
	}

	/**
	 * Prompts user with confirmation for fixtures that should be unloaded.
	 * @param array $fixtures
	 * @param array $except
	 * @return boolean
	 */
	private function confirmClear($fixtures, $except)
	{
		$this->stdout("Fixtures namespace is: \n", Console::FG_YELLOW);
		$this->stdout("\t" . $this->namespace . "\n\n", Console::FG_GREEN);

		$this->stdout("Fixtures below will be unloaded:\n\n", Console::FG_YELLOW);
		$this->outputList($fixtures);

		if (count($except)) {
			$this->stdout("\nFixtures that will NOT be unloaded:\n\n", Console::FG_YELLOW);
			$this->outputList($except);
		}

		return $this->confirm("\nUnload fixtures?");
	}

	/**
	 * Outputs data to the console as a list.
	 * @param array $data
	 */
	private function outputList($data)
	{
		foreach ($data as $index => $item) {
			$this->stdout("\t" . ($index + 1) . ". {$item}\n", Console::FG_GREEN);
		}
	}

	/**
	 * Checks if needed to apply all fixtures.
	 * @param string $fixture
	 * @return bool
	 */
	public function needToApplyAll($fixture)
	{
		return $fixture == self::APPLY_ALL;
	}

	/**
	 * @param array $fixtures
	 * @return array Array of found fixtures. These may differ from input parameter as not all fixtures may exists.
	 */
	private function findFixtures(array $fixtures)
	{
		$fixturesPath = Yii::getAlias($this->getFixturePath());

		$filesToSearch = ['*Fixture.php'];
		if (!$this->needToApplyAll($fixtures[0])) {
			$filesToSearch = [];
			foreach ($fixtures as $fileName) {
				$filesToSearch[] = $fileName . 'Fixture.php';
			}
		}

		$files = FileHelper::findFiles($fixturesPath, ['only' => $filesToSearch]);
		$foundFixtures = [];

		foreach ($files as $fixture) {
			$foundFixtures[] = basename($fixture, 'Fixture.php');
		}

		return $foundFixtures;
	}

	/**
	 * Returns valid fixtures config that can be used to load them.
	 * @param array $fixtures fixtures to configure
	 * @return array
	 */
	private function getFixturesConfig($fixtures)
	{
		$config = [];

		foreach ($fixtures as $fixture) {

			$fullClassName = $this->namespace . '\\' . $fixture . 'Fixture';

			if (class_exists($fullClassName)) {
				$config[Inflector::camel2id($fixture, '_')] = [
					'class' => $fullClassName,
				];
			}
		}

		return $config;
	}

	/**
	 * Returns fixture path that determined on fixtures namespace.
	 * @return string fixture path
	 */
	private function getFixturePath()
	{
		return Yii::getAlias('@' . str_replace('\\', '/', $this->namespace));
	}

}
