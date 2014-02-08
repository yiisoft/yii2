<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\faker;

use Yii;
use yii\console\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Console;

/**
 * This command manage fixtures creations based on given template.
 *
 * Fixtures are one of the important paths in unit testing. To speed up developers
 * work these fixtures can be generated automatically, based on prepared template.
 * This command is a simple wrapper for the fixtures library [Faker](https://github.com/fzaninotto/Faker).
 * 
 * You should configure this command as follows (you can use any alias, not only "faker:fixture"):
 * 
 * ~~~
 *	'controllerMap' => [
 *		'faker' => [
 *			'class' => 'yii\faker\FixtureController',
 *		],
 *	],
 * ~~~
 * 
 * To start using this command you need to be familiar (read guide) for the Faker library and
 * generate fixtures template files, according to the given format:
 * 
 * ~~~
 * #users.php file under $templatePath
 * 
 * return [
 *	[
 *		'table_column0' => 'faker_formatter',
 *		...
 *		'table_columnN' => 'other_faker_formatter
 *		'table_columnN+1' => function ($fixture, $faker, $index) {
 *			//set needed fixture fields based on different conditions
 *			return $fixture;
 *		}
 *	],
 * ];
 * ~~~
 * 
 * If you use callback as a attribute value, then it will be called as shown with three parameters:
 * - $fixture - current fixture array. 
 * - $faker - faker generator instance
 * - $index - current fixture index. For example if user need to generate 3 fixtures for tbl_user, it will be 0..2
 * After you set all needed fields in callback, you need to return $fixture array back from the callback.
 * 
 * After you prepared needed templates for tables you can simply generate your fixtures via command
 * 
 * ~~~
 * php yii faker/generate users
 * 
 * //also a short version of this command (generate action is default)
 * php yii faker users
 * ~~~
 * 
 * In the code above "users" is template name, after this command run, new file named same as template
 * will be created under the $fixturesPath folder.
 * You can generate fixtures for all templates by specifying keyword "all_fixtures"
 * 
 * ~~~
 * php yii faker/generate all_fixtures
 * ~~~
 * 
 * This command will generate fixtures for all template files that are stored under $templatePath and 
 * store fixtures under $fixturesPath with file names same as templates names.
 * 
 * You can specify how many fixtures per file you need by the second parameter. In the code below we generate
 * all fixtures and in each file there will be 3 rows (fixtures).
 * 
 * ~~~
 * php yii faker/generate all_fixtures 3
 * ~~~
 * 
 * You can specify different options of this command:
 * 
 * ~~~
 * //generate fixtures in russian languge
 * php yii faker/generate users 5 --language='ru_RU'
 * 
 * //read templates from the other path
 * php yii faker/generate all_fixtures --templatePath='@app/path/to/my/custom/templates'
 * 
 * //generate fixtures into other folders, but be sure that this folders exists or you will get notice about that.
 * php yii faker/generate all_fixtures --fixturesPath='@tests/unit/fixtures/subfolder1/subfolder2/subfolder3'
 * ~~~
 * 
 * You also can create your own data providers for custom tables fields, see Faker library guide for more info (https://github.com/fzaninotto/Faker);
 * After you created custom provider, for example:
 * 
 * ~~~
 * 
 *	class Book extends \Faker\Provider\Base
 *	{
 *		public function title($nbWords = 5)
 *		{
 *			$sentence = $this->generator->sentence($nbWords);
 *			return mb_substr($sentence, 0, mb_strlen($sentence) - 1);
 *		}
 *
 *		public function ISBN()
 *		{
 *			return $this->generator->randomNumber(13);
 *		}
 *	}
 * ~~~
 * 
 * you can use it by adding it to the $providers property of the current command. In your console.php config:
 * 
 * ~~~
 *	'controllerMap' => [
 *		'faker' => [
 *			'class' => 'yii\faker\FixtureController',
 *			'providers' => [
 *				'app\tests\unit\faker\providers\Book',
 *			],
 *		],
 *	],
 * ~~~
 * 
 * @property \Faker\Generator $generator
 * 
 * @since 2.0.0
 */
class FixtureController extends \yii\console\controllers\FixtureController
{

	/**
	 * type of fixture generating
	 */
	const GENERATE_ALL = 'all_fixtures';

	/**
	 * @var string controller default action ID.
	 */
	public $defaultAction = 'generate';

	/**
	 * Alias to the template path, where all tables templates are stored.
	 * @var string
	 */
	public $templatePath = '@tests/unit/templates/fixtures';

	/**
	 * Language to use when generating fixtures data.
	 * @var string
	 */
	public $language;

	/**
	 * Additional data providers that can be created by user and will be added to the Faker generator.
	 * More info in [Faker](https://github.com/fzaninotto/Faker.) library docs.
	 * @var array
	 */
	public $providers = [];

	/**
	 * Faker generator instance
	 * @var \Faker\Generator
	 */
	private $_generator;

	/**
	 * Returns the names of the global options for this command.
	 * @return array the names of the global options for this command.
	 */
	public function globalOptions()
	{
		return array_merge(parent::globalOptions(), [
			'templatePath','language'
		]);
	}

	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {
			$this->checkPaths();
			$this->addProviders();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Generates fixtures and fill them with Faker data.
	 * @param string $file filename for the table template. You can generate all fixtures for all tables
	 * by specifiyng keyword "all_fixtures" as filename.
	 * @param integer $times how much fixtures do you want per table
	 */
	public function actionGenerate($file, $times = 2)
	{
		$templatePath = Yii::getAlias($this->templatePath);
		$fixturesPath = Yii::getAlias($this->fixturesPath);

		if ($this->needToGenerateAll($file)) {
			$files = FileHelper::findFiles($templatePath, ['only' => ['.php']]);
		}
		else {
			foreach(explode(',', $file) as $fileName) {
				$filesToSearch[] = $fileName . '.php';
			}
			$files = FileHelper::findFiles($templatePath, ['only' => $filesToSearch]);
		}

		if (empty($files)) {
			throw new Exception(
					"No files were found by name: \"{$file}\". \n"
					. "Check that template with these name exists, under template path: \n\"{$templatePath}\"."
			);
		}

		if (!$this->confirmGeneration($files)) {
			return;
		}

		foreach ($files as $templateFile) {
			$fixtureFileName = basename($templateFile);
			$template = $this->getTemplate($templateFile);
			$fixtures = [];

			for ($i = 0; $i < $times; $i++) {
				$fixtures[$i] = $this->generateFixture($template, $i);
			}

			$content = $this->getExportedFormat($fixtures); 
			file_put_contents($fixturesPath.'/'.$fixtureFileName, $content);
			$this->stdout("Fixture file was generated under: " . realpath($fixturesPath . "/" . $fixtureFileName) . "\n", Console::FG_GREEN);
		}
	}

	/**
	 * Returns Faker generator instance. Getter for private property.
	 * @return \Faker\Generator
	 */
	public function getGenerator()
	{
		if (is_null($this->_generator)) {
			//replacing - on _ because Faker support only en_US format and not intl

			$language = is_null($this->language) ? str_replace('-','_', Yii::$app->language) : $this->language;
			$this->_generator = \Faker\Factory::create($language);
		}

		return $this->_generator;
	}

	/**
	 * Check if the template path and migrations path exists and writable.
	 */
	public function checkPaths()
	{
		$path = Yii::getAlias($this->templatePath);

		if (!is_dir($path)) {
			throw new Exception("The template path \"{$this->templatePath}\" not exist");
		}
	}

	/**
	 * Adds users providers to the faker generator.
	 */
	public function addProviders()
	{
		foreach($this->providers as $provider) {
			$this->generator->addProvider(new $provider($this->generator));
		}
	}

	/**
	 * Checks if needed to generate all fixtures.
	 * @param string $file
	 * @return bool
	 */
	public function needToGenerateAll($file)
	{
		return $file == self::GENERATE_ALL;
	}

	/**
	 * Returns generator template for the given fixture name
	 * @param string $file template file
	 * @return array generator template
	 * @throws \yii\console\Exception if wrong file format
	 */
	public function getTemplate($file)
	{
		$template = require($file);

		if (!is_array($template)) {
			throw new Exception("The template file \"$file\" has wrong format. It should return valid template array");
		}

		return $template;
	}

	/**
	 * Returns exported to the string representation of given fixtures array.
	 * @param type $fixtures
	 * @return string exported fixtures format
	 */
	public function getExportedFormat($fixtures)
	{
		$content = "<?php\n\nreturn [";

		foreach($fixtures as $fixture) {

			$content .= "\n\t[";

			foreach($fixture as $name=>$value) {
				$content .= "\n\t\t'{$name}' => '{$value}',";
			}

			$content .= "\n\t],";

		}
		$content .= "\n];\n";
		return $content;
	}

	/**
	 * Generates fixture from given template
	 * @param array $template fixture template
	 * @param integer $index current fixture index
	 * @return array fixture
	 */
	public function generateFixture($template, $index)
	{
		$fixture = [];

		foreach($template as $attribute => $fakerProperty) {
			if (!is_string($fakerProperty)) {
				$fixture = call_user_func_array($fakerProperty,[$fixture,$this->generator, $index]);
			} else {
				$fixture[$attribute] = $this->generator->$fakerProperty;
			}
		}

		return $fixture;
	}

	/**
	 * Prompts user with message if he confirm generation with given fixture templates files.
	 * @param array $files
	 * @return boolean
	 */
	public function confirmGeneration($files)
	{
		$this->stdout("Fixtures will be generated under the path: \n", Console::FG_YELLOW);
		$this->stdout(realpath(Yii::getAlias($this->fixturesPath, false)) ."\n\n", Console::FG_GREEN);
		$this->stdout("Templates will be taken from path: \n", Console::FG_YELLOW);
		$this->stdout(realpath(Yii::getAlias($this->templatePath, false)) . "\n\n", Console::FG_GREEN);

		foreach ($files as $index => $fileName) {
			$this->stdout("    " . $index +1 . ". " . basename($fileName) . "\n",Console::FG_GREEN);
		}
		return $this->confirm('Generate above fixtures?');
	}

}
