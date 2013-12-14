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

/**
 * This command manage fixtures creations based on given template.
 * 
 * Fixtures are one of the important paths in unit testing. To speed up developers
 * work this fixtures can be generated automatically, based on prepared template.
 * This command is a simple wrapper for the fixtures library Faker (https://github.com/fzaninotto/Faker).
 * 
 * You should configure this command as follows (you can use any alias, not only "faker:fixture"):
 * ~~~
 *	'controllerMap'	=>	[
 *		'faker:fixture'		=>	[
 *			'class'			=>	'yii\faker\FixtureController',
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
 *		'table_column0'	=>	'faker_formatter',
 *		...
 *		'table_columnN'	=>	'other_faker_formatter
 *	],
 * ];
 * ~~~
 * 
 * After you prepared needed templates for tables you can simply generate your fixtures via command
 * 
 * ~~~
 * php yii faker:fixture/generate users
 * ~~~
 * 
 * In the code above "users" is template name, after this command run, new file named same as template
 * will be created under the $fixturesPath folder.
 * You can generate fixtures for all templates by specifying keyword "all_fixtures"
 * 
 * ~~~
 * php yii faker:fixture/generate all_fixtures
 * ~~~
 * 
 * This command will generate fixtures for all template files that are stored under $templatePath and 
 * store fixtures under $fixturesPath with file names same as templates names.
 * 
 * You can specify how many fixtures per file you need by the second parameter. In the code below we generate
 * all fixtures and in each file there will be 3 rows (fixtures).
 * 
 * ~~~
 * php yii faker:fixture/generate all_fixtures 3
 * ~~~
 * 
 * You can specify different options of this command:
 * 
 * ~~~
 * #generate fixtures in russian languge
 * php yii faker:fixture/generate users 5 --language='ru_RU'
 * 
 * #read templates from the other path
 * php yii faker:fixture/generate all_fixtures --templatePath='@app/path/to/my/custom/templates'
 * 
 * #generate fixtures into other folders, but be sure that this folders exists or you will get notice about that.
 * php yii faker:fixture/generate all_fixtures --fixturesPath='@tests/unit/fixtures/subfolder1/subfolder2/subfolder3'
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
 *			return substr($sentence, 0, strlen($sentence) - 1);
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
 *	'controllerMap'	=>	[
 *		'faker:fixture'		=>	[
 *			'class'			=>	'yii\faker\FixtureController',
 *			'providers'		=>	[
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
class FixtureController extends \yii\console\Controller
{

	/**
	 * Alias to the template path, where all tables templates are stored.
	 * @var string
	 */
	public $templatePath = '@tests/unit/fixtures/templates';

	/**
	 * Alias to the path, where all fixtures are stored.
	 * @var string
	 */
	public $fixturesPath = '@tests/unit/fixtures';

	/**
	 * Language to use when generating fixtures data.
	 * @var string
	 */
	public $language;

	/**
	 * Additional data providers that can be created by user and will be added to the Faker generator.
	 * More info in Faker library docs https://github.com/fzaninotto/Faker.
	 * @var array
	 */
	public $providers = array();

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
			'templatePath','fixturesPath','language'
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

		if ($this->needToGenerateAll($file))
			$files = FileHelper::findFiles($templatePath, ['only' => ['.php']]);
		else
			$files = FileHelper::findFiles($templatePath, ['only' => [$file.'.php']]);

		foreach ($files as $templateFile)
		{
			$fixtureFileName = basename($templateFile);
			$template = require($templateFile);
			$content = "<?php\n\nreturn [";

			for ($i = 0; $i < $times; $i++)
			{
				$content .= "\n\t[";

				foreach($template as $attribute => $fakerProperty)
				{
					$content .= "\n\t\t'{$attribute}' => \"{$this->generator->$fakerProperty}\",";
				}

				$content .= "\n\t],";
			}

			$content .= "\n];";
			file_put_contents($fixturesPath.'/'.$fixtureFileName.'.php', $content);
		}
	}

	/**
	 * Returns Faker generator instance. Getter for private property.
	 * @return \Faker\Generator
	 */
	public function getGenerator()
	{
		if (is_null($this->_generator))
		{
			$language = is_null($this->language) ? Yii::$app->language : $this->language;
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

		if (!is_dir($path))
			throw new Exception("The template path \"{$this->templatePath}\" not exist");

		$path = Yii::getAlias($this->fixturesPath);

		if (!is_dir($path) || !is_writable($path))
			throw new Exception("The fixtures path \"{$this->templatePath}\" not exist or is not writable");
	}

	/**
	 * Adds users providers to the faker generator.
	 */
	public function addProviders()
	{
		foreach($this->providers as $provider)
			$this->generator->addProvider(new $provider($this->generator));
	}

	/**
	 * Checks if needed to generate all fixtures.
	 * @param string $file
	 * @return bool
	 */
	public function needToGenerateAll($file)
	{
		return $file == 'all_fixtures';
	}

}
