<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\faker;

use Yii;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

/**
 * This command manage fixtures creations based on given template.
 *
 * Fixtures are one of the important paths in unit testing. To speed up developers
 * work these fixtures can be generated automatically, based on prepared template.
 * This command is a simple wrapper for the fixtures library [Faker](https://github.com/fzaninotto/Faker).
 *
 * You should configure this command as follows (you can use any alias, not only "fixture"):
 *
 * ~~~
 * 'controllerMap' => [
 *     'fixture' => [
 *         'class' => 'yii\faker\FixtureController',
 *     ],
 * ],
 * ~~~
 *
 * To start using this command you need to be familiar (read guide) for the Faker library and
 * generate fixtures template files, according to the given format:
 *
 * ```php
 * // users.php file under template path (by default @tests/unit/templates/fixtures)
 * return [
 *     'name' => $faker->firstName,
 *     'phone' => $faker->phoneNumber,
 *     'city' => $faker->city,
 *     'password' => Yii::$app->getSecurity()->generatePasswordHash('password_' . $index),
 *     'auth_key' => Yii::$app->getSecurity()->generateRandomString(),
 *     'intro' => $faker->sentence(7, true),  // generate a sentence with 7 words
 * ];
 * ```
 *
 * If you use callback as a attribute value, then it will be called as shown with three parameters:
 *
 * - `$faker`: the Faker generator instance
 * - `$index`: the current fixture index. For example if user need to generate 3 fixtures for user table, it will be 0..2.
 *
 * After you set all needed fields in callback, you need to return $fixture array back from the callback.
 *
 * After you prepared needed templates for tables you can simply generate your fixtures via command
 *
 * ~~~
 * yii fixture/generate user
 *
 * //generate fixtures from several templates, for example:
 * yii fixture/generate user profile team
 * ~~~
 *
 * In the code above "users" is template name, after this command run, new file named same as template
 * will be created under the `$fixtureDataPath` folder.
 * You can generate fixtures for all templates, for example:
 *
 * ~~~
 * yii fixture/generate-all
 * ~~~
 *
 * This command will generate fixtures for all template files that are stored under $templatePath and
 * store fixtures under `$fixtureDataPath` with file names same as templates names.
 *
 * You can specify how many fixtures per file you need by the second parameter. In the code below we generate
 * all fixtures and in each file there will be 3 rows (fixtures).
 *
 * ~~~
 * yii fixture/generate-all --count=3
 * ~~~
 *
 * You can specify different options of this command:
 *
 * ~~~
 * //generate fixtures in russian language
 * yii fixture/generate user --count=5 --language=ru_RU
 *
 * //read templates from the other path
 * yii fixture/generate-all --templatePath=@app/path/to/my/custom/templates
 *
 * //generate fixtures into other folders
 * yii fixture/generate-all --fixtureDataPath=@tests/unit/fixtures/subfolder1/subfolder2/subfolder3
 * ~~~
 *
 * You can see all available templates by running command:
 *
 * ~~~
 * //list all templates under default template path (i.e. '@tests/unit/templates/fixtures')
 * yii fixture/templates
 *
 * //list all templates under specified template path
 * yii fixture/templates --templatePath='@app/path/to/my/custom/templates'
 * ~~~
 *
 * You also can create your own data providers for custom tables fields, see Faker library guide for more info (https://github.com/fzaninotto/Faker);
 * After you created custom provider, for example:
 *
 * ~~~
 * class Book extends \Faker\Provider\Base
 * {
 *
 *     public function title($nbWords = 5)
 *     {
 *         $sentence = $this->generator->sentence($nbWords);
 *         return mb_substr($sentence, 0, mb_strlen($sentence) - 1);
 *     }
 *
 * }
 * ~~~
 *
 * you can use it by adding it to the $providers property of the current command. In your console.php config:
 *
 * ~~~
 *    'controllerMap' => [
 *        'fixture' => [
 *            'class' => 'yii\faker\FixtureController',
 *            'providers' => [
 *                'app\tests\unit\faker\providers\Book',
 *            ],
 *        ],
 *    ],
 * ~~~
 *
 * @property \Faker\Generator $generator This property is read-only.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0.0
 */
class FixtureController extends \yii\console\controllers\FixtureController
{
    /**
     * @var string Alias to the template path, where all tables templates are stored.
     */
    public $templatePath = '@tests/unit/templates/fixtures';
    /**
     * @var string Alias to the fixture data path, where data files should be written.
     */
    public $fixtureDataPath = '@tests/unit/fixtures/data';
    /**
     * @var string Language to use when generating fixtures data.
     */
    public $language;
    /**
     * @var integer total count of data per fixture. Defaults to 2.
     */
    public $count = 2;
    /**
     * @var array Additional data providers that can be created by user and will be added to the Faker generator.
     * More info in [Faker](https://github.com/fzaninotto/Faker.) library docs.
     */
    public $providers = [];

    /**
     * @var \Faker\Generator Faker generator instance
     */
    private $_generator;


    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'templatePath', 'language', 'fixtureDataPath', 'count'
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
     * Lists all available fixtures template files.
     */
    public function actionTemplates()
    {
        $foundTemplates = $this->findTemplatesFiles();

        if (!$foundTemplates) {
            $this->notifyNoTemplatesFound();
        } else {
            $this->notifyTemplatesCanBeGenerated($foundTemplates);
        }
    }

    /**
     * Generates fixtures and fill them with Faker data.
     * For example,
     * 
     * ~~~
     * //generate fixtures in russian language
     * yii fixture/generate user --count=5 --language=ru_RU
     *
     * //generate several fixtures
     * yii fixture/generate user profile team
     * ~~~
     * 
     * @throws \yii\base\InvalidParamException
     * @throws \yii\console\Exception
     */
    public function actionGenerate()
    {
        $templatesInput = func_get_args();

        if (empty($templatesInput)) {
            throw new Exception('You should specify input fixtures template files');
        }

        $foundTemplates = $this->findTemplatesFiles($templatesInput);

        $notFoundTemplates = array_diff($templatesInput, $foundTemplates);

        if ($notFoundTemplates) {
            $this->notifyNotFoundTemplates($notFoundTemplates);
        }

        if (!$foundTemplates) {
            $this->notifyNoTemplatesFound();
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmGeneration($foundTemplates)) {
            return static::EXIT_CODE_NORMAL;
        }

        $templatePath = Yii::getAlias($this->templatePath);
        $fixtureDataPath = Yii::getAlias($this->fixtureDataPath);

        FileHelper::createDirectory($fixtureDataPath);

        $generatedTemplates = [];

        foreach ($foundTemplates as $templateName) {
            $this->generateFixtureFile($templateName, $templatePath, $fixtureDataPath);
            $generatedTemplates[] = $templateName;
        }

        $this->notifyTemplatesGenerated($generatedTemplates);
    }

    /**
     * Generates all fixtures template path that can be found.
     */
    public function actionGenerateAll()
    {
        $foundTemplates = $this->findTemplatesFiles();

        if (!$foundTemplates) {
            $this->notifyNoTemplatesFound();
            return static::EXIT_CODE_NORMAL;
        }

        if (!$this->confirmGeneration($foundTemplates)) {
            return static::EXIT_CODE_NORMAL;
        }

        $templatePath = Yii::getAlias($this->templatePath);
        $fixtureDataPath = Yii::getAlias($this->fixtureDataPath);

        FileHelper::createDirectory($fixtureDataPath);

        $generatedTemplates = [];

        foreach ($foundTemplates as $templateName) {
            $this->generateFixtureFile($templateName, $templatePath, $fixtureDataPath);            
            $generatedTemplates[] = $templateName;
        }

        $this->notifyTemplatesGenerated($generatedTemplates);
    }

    /**
     * Notifies user that given fixtures template files were not found.
     * @param array $templatesNames
     */
    private function notifyNotFoundTemplates($templatesNames)
    {
        $this->stdout("The following fixtures templates were NOT found:\n\n", Console::FG_RED);

        foreach ($templatesNames as $name) {
            $this->stdout("\t * $name \n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    /**
     * Notifies user that there was not found any files matching given input conditions.
     */
    private function notifyNoTemplatesFound()
    {
        $this->stdout("No fixtures template files matching input conditions were found under the path:\n\n", Console::FG_RED);
        $this->stdout("\t " . Yii::getAlias($this->templatePath) . " \n\n", Console::FG_GREEN);
    }

    /**
     * Notifies user that given fixtures template files were generated.
     * @param array $templatesNames
     */
    private function notifyTemplatesGenerated($templatesNames)
    {
        $this->stdout("The following fixtures template files were generated:\n\n", Console::FG_YELLOW);

        foreach ($templatesNames as $name) {
            $this->stdout("\t* " . $name . "\n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    private function notifyTemplatesCanBeGenerated($templatesNames)
    {
        $this->stdout("Template files path: ", Console::FG_YELLOW);
        $this->stdout(Yii::getAlias($this->templatePath) . "\n\n", Console::FG_GREEN);

        foreach ($templatesNames as $name) {
            $this->stdout("\t* " . $name . "\n", Console::FG_GREEN);
        }

        $this->stdout("\n");
    }

    /**
     * Returns array containing fixtures templates file names. You can specify what files to find
     * by the given parameter.
     * @param array $templatesNames template file names to search. If empty then all files will be searched.
     * @return array
     */
    private function findTemplatesFiles(array $templatesNames = [])
    {
        $findAll = ($templatesNames == []);

        if ($findAll) {
            $files = FileHelper::findFiles(Yii::getAlias($this->templatePath), ['only' => ['*.php']]);
        } else {
            $filesToSearch = [];

            foreach ($templatesNames as $fileName) {
                $filesToSearch[] = $fileName . '.php';
            }

            $files = FileHelper::findFiles(Yii::getAlias($this->templatePath), ['only' => $filesToSearch]);
        }

        $foundTemplates = [];

        foreach ($files as $fileName) {
            $foundTemplates[] = basename($fileName, '.php');
        }

        return $foundTemplates;
    }

    /**
     * Returns Faker generator instance. Getter for private property.
     * @return \Faker\Generator
     */
    public function getGenerator()
    {
        if ($this->_generator === null) {
            $language = $this->language === null ? Yii::$app->language : $this->language;
            $this->_generator = \Faker\Factory::create(str_replace('-', '_', $language));
        }
        return $this->_generator;
    }

    /**
     * Check if the template path and migrations path exists and writable.
     */
    public function checkPaths()
    {
        $path = Yii::getAlias($this->templatePath, false);

        if (!$path || !is_dir($path)) {
            throw new Exception("The template path \"{$this->templatePath}\" does not exist");
        }
    }

    /**
     * Adds users providers to the faker generator.
     */
    public function addProviders()
    {
        foreach ($this->providers as $provider) {
            $this->generator->addProvider(new $provider($this->generator));
        }
    }

    /**
     * Returns exported to the string representation of given fixtures array.
     * @param array $fixtures
     * @return string exported fixtures format
     */
    public function exportFixtures($fixtures)
    {
        return "<?php\n\nreturn " . VarDumper::export($fixtures) . ";\n";
    }

    /**
     * Generates fixture from given template
     * @param string $_template_ the fixture template file
     * @param integer $index the current fixture index
     * @return array fixture
     */
    public function generateFixture($_template_, $index)
    {
        // $faker and $index are exposed to the template file
        $faker = $this->getGenerator();
        return require($_template_);
    }

    /**
     * Generates fixture file by the given fixture template file.
     * @param string $templateName template file name
     * @param string $templatePath path where templates are stored
     * @param string $fixtureDataPath fixture data path where generated file should be written
     */
    public function generateFixtureFile($templateName, $templatePath, $fixtureDataPath)
    {
        $fixtures = [];

        for ($i = 0; $i < $this->count; $i++) {
            $fixtures[$i] = $this->generateFixture($templatePath . '/' . $templateName . '.php', $i);
        }

        $content = $this->exportFixtures($fixtures);

        file_put_contents($fixtureDataPath . '/'. $templateName . '.php', $content);
    }

    /**
     * Prompts user with message if he confirm generation with given fixture templates files.
     * @param array $files
     * @return boolean
     */
    public function confirmGeneration($files)
    {
        $this->stdout("Fixtures will be generated under the path: \n", Console::FG_YELLOW);
        $this->stdout("\t" . Yii::getAlias($this->fixtureDataPath) . "\n\n", Console::FG_GREEN);
        $this->stdout("Templates will be taken from path: \n", Console::FG_YELLOW);
        $this->stdout("\t" . Yii::getAlias($this->templatePath) . "\n\n", Console::FG_GREEN);

        foreach ($files as $fileName) {
            $this->stdout("\t* " . $fileName . "\n", Console::FG_GREEN);
        }

        return $this->confirm('Generate above fixtures?');
    }

}
