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
use yii\console\ExitCode;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\i18n\DbMessageSource;
use yii\i18n\GettextMessageSource;
use yii\i18n\GettextPoFile;
use yii\i18n\PhpMessageSource;

/**
 * Extracts messages to be translated from source files.
 *
 * The extracted messages can be saved the following depending on `format`
 * setting in config file:
 *
 * - PHP message source files.
 * - ".po" files.
 * - Database.
 *
 * Usage:
 * 1. Create a configuration file using the 'message/config' command:
 *    yii message/config /path/to/myapp/messages/config.php
 * 2. Edit the created config file, adjusting it for your web application needs.
 * 3. Run the 'message/extract' command, using created config:
 *    yii message /path/to/myapp/messages/config.php
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MessageController extends \yii\console\Controller
{
    /**
     * @var string controller default action ID.
     */
    public $defaultAction = 'extract';
    /**
     * @var string required, root directory of all source files.
     */
    public $sourcePath = '@yii';
    /**
     * @var string required, root directory containing message translations.
     */
    public $messagePath = '@yii/messages';
    /**
     * @var array required, list of language codes that the extracted messages
     * should be translated to. For example, ['zh-CN', 'de'].
     */
    public $languages = [];
    /**
     * @var string the name of the function for translating messages.
     * Defaults to 'Yii::t'. This is used as a mark to find the messages to be
     * translated. You may use a string for single function name or an array for
     * multiple function names.
     */
    public $translator = 'Yii::t';
    /**
     * @var bool whether to sort messages by keys when merging new messages
     * with the existing ones. Defaults to false, which means the new (untranslated)
     * messages will be separated from the old (translated) ones.
     */
    public $sort = false;
    /**
     * @var bool whether the message file should be overwritten with the merged messages
     */
    public $overwrite = true;
    /**
     * @var bool whether to remove messages that no longer appear in the source code.
     * Defaults to false, which means these messages will NOT be removed.
     */
    public $removeUnused = false;
    /**
     * @var bool whether to mark messages that no longer appear in the source code.
     * Defaults to true, which means each of these messages will be enclosed with a pair of '@@' marks.
     */
    public $markUnused = true;
    /**
     * @var array list of patterns that specify which files/directories should NOT be processed.
     * If empty or not set, all files/directories will be processed.
     * See helpers/FileHelper::findFiles() description for pattern matching rules.
     * If a file/directory matches both a pattern in "only" and "except", it will NOT be processed.
     */
    public $except = [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/BaseYii.php', // contains examples about Yii:t()
    ];
    /**
     * @var array list of patterns that specify which files (not directories) should be processed.
     * If empty or not set, all files will be processed.
     * See helpers/FileHelper::findFiles() description for pattern matching rules.
     * If a file/directory matches both a pattern in "only" and "except", it will NOT be processed.
     */
    public $only = ['*.php'];
    /**
     * @var string generated file format. Can be "php", "db", "po" or "pot".
     */
    public $format = 'php';
    /**
     * @var string connection component ID for "db" format.
     */
    public $db = 'db';
    /**
     * @var string custom name for source message table for "db" format.
     */
    public $sourceMessageTable = '{{%source_message}}';
    /**
     * @var string custom name for translation message table for "db" format.
     */
    public $messageTable = '{{%message}}';
    /**
     * @var string name of the file that will be used for translations for "po" format.
     */
    public $catalog = 'messages';
    /**
     * @var array message categories to ignore. For example, 'yii', 'app*', 'widgets/menu', etc.
     * @see isCategoryIgnored
     */
    public $ignoreCategories = [];
    /**
     * @var string File header in generated PHP file with messages. This property is used only if [[$format]] is "php".
     * @since 2.0.13
     */
    public $phpFileHeader = '';
    /**
     * @var string|null DocBlock used for messages array in generated PHP file. If `null`, default DocBlock will be used.
     * This property is used only if [[$format]] is "php".
     * @since 2.0.13
     */
    public $phpDocBlock;

    /**
     * @var array Config for messages extraction.
     * @see actionExtract()
     * @see initConfig()
     * @since 2.0.13
     */
    protected $config;


    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'sourcePath',
            'messagePath',
            'languages',
            'translator',
            'sort',
            'overwrite',
            'removeUnused',
            'markUnused',
            'except',
            'only',
            'format',
            'db',
            'sourceMessageTable',
            'messageTable',
            'catalog',
            'ignoreCategories',
            'phpFileHeader',
            'phpDocBlock',
        ]);
    }

    /**
     * @inheritdoc
     * @since 2.0.8
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'c' => 'catalog',
            'e' => 'except',
            'f' => 'format',
            'i' => 'ignoreCategories',
            'l' => 'languages',
            'u' => 'markUnused',
            'p' => 'messagePath',
            'o' => 'only',
            'w' => 'overwrite',
            'S' => 'sort',
            't' => 'translator',
            'm' => 'sourceMessageTable',
            's' => 'sourcePath',
            'r' => 'removeUnused',
        ]);
    }

    /**
     * Creates a configuration file for the "extract" command using command line options specified.
     *
     * The generated configuration file contains parameters required
     * for source code messages extraction.
     * You may use this configuration file with the "extract" command.
     *
     * @param string $filePath output file name or alias.
     * @return int CLI exit code
     * @throws Exception on failure.
     */
    public function actionConfig($filePath)
    {
        $filePath = Yii::getAlias($filePath);
        if (file_exists($filePath)) {
            if (!$this->confirm("File '{$filePath}' already exists. Do you wish to overwrite it?")) {
                return ExitCode::OK;
            }
        }

        $array = VarDumper::export($this->getOptionValues($this->action->id));
        $content = <<<EOD
<?php
/**
 * Configuration file for 'yii {$this->id}/{$this->defaultAction}' command.
 *
 * This file is automatically generated by 'yii {$this->id}/{$this->action->id}' command.
 * It contains parameters for source code messages extraction.
 * You may modify this file to suit your needs.
 *
 * You can use 'yii {$this->id}/{$this->action->id}-template' command to create
 * template configuration file with detailed description for each parameter.
 */
return $array;

EOD;

        if (file_put_contents($filePath, $content) === false) {
            $this->stdout("Configuration file was NOT created: '{$filePath}'.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Configuration file created: '{$filePath}'.\n\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Creates a configuration file template for the "extract" command.
     *
     * The created configuration file contains detailed instructions on
     * how to customize it to fit for your needs. After customization,
     * you may use this configuration file with the "extract" command.
     *
     * @param string $filePath output file name or alias.
     * @return int CLI exit code
     * @throws Exception on failure.
     */
    public function actionConfigTemplate($filePath)
    {
        $filePath = Yii::getAlias($filePath);

        if (file_exists($filePath)) {
            if (!$this->confirm("File '{$filePath}' already exists. Do you wish to overwrite it?")) {
                return ExitCode::OK;
            }
        }

        if (!copy(Yii::getAlias('@yii/views/messageConfig.php'), $filePath)) {
            $this->stdout("Configuration file template was NOT created at '{$filePath}'.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Configuration file template created at '{$filePath}'.\n\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Extracts messages to be translated from source code.
     *
     * This command will search through source code files and extract
     * messages that need to be translated in different languages.
     *
     * @param string $configFile the path or alias of the configuration file.
     * You may use the "yii message/config" command to generate
     * this file and then customize it for your needs.
     * @return int exit code.
     * @throws Exception on failure.
     */
    public function actionExtract($configFile = null)
    {
        $this->initConfig($configFile);

        $files = FileHelper::findFiles(realpath($this->config['sourcePath']), $this->config);

        $messages = [];
        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $this->config['translator'], $this->config['ignoreCategories']));
        }

        if (empty($this->config['format'])) {
            foreach ($messages as $category => $categoryMessages) {
                $messageSource = Yii::$app->getI18n()->getMessageSource($category);
                foreach ($this->config['languages'] as $language) {
                    $this->stdout("Saving messages: category={$category}, language={$language}...\n");
                    try {
                        $changeCount = $messageSource->save($category, $language, array_fill_keys($categoryMessages, ''), $this->config);
                        if ($changeCount < 1) {
                            $this->stdout("Nothing to save.\n\n", Console::FG_GREEN);
                        } else {
                            $this->stdout("Translation saved.\n\n", Console::FG_GREEN);
                        }
                    } catch (\Throwable $e) {
                        $this->stdout("Unable to save translation for category={$category}, language={$language}.\n\n", Console::FG_RED);
                    }
                }
            }

            return ExitCode::OK;
        }

        /* @var $messageSource \yii\i18n\MessageSource */
        $options = $this->config;
        switch ($this->config['format']) {
            case 'php':
                $messageSource = Yii::createObject([
                    'class' => PhpMessageSource::class,
                    'basePath' => $this->config['messagePath']
                ]);
                break;
            case 'db':
                $messageSource = Yii::createObject([
                    'class' => DbMessageSource::class,
                    'db' => $this->config['db'],
                    'sourceMessageTable' => isset($this->config['sourceMessageTable']) ? $this->config['sourceMessageTable'] : '{{%source_message}}',
                    'messageTable' => isset($this->config['messageTable']) ? $this->config['messageTable'] : '{{%message}}',
                ]);
                break;
            case 'po':
            case 'mo':
            case 'pot':
                $messageSource = Yii::createObject([
                    'class' => GettextMessageSource::class,
                    'basePath' => $this->config['messagePath']
                ]);
                break;
            default:
                throw new InvalidConfigException("Unknown format '{$this->config['format']}'");
        }

        foreach ($messages as $category => $categoryMessages) {
            foreach ($this->config['languages'] as $language) {
                $this->stdout("Saving messages: category={$category}, language={$language}...\n");
                try {
                    $changeCount = $messageSource->save($category, $language, array_fill_keys($categoryMessages, ''), $options);
                    if ($changeCount < 1) {
                        $this->stdout("Nothing to save.\n\n", Console::FG_GREEN);
                    } else {
                        $this->stdout("Translation saved.\n\n", Console::FG_GREEN);
                    }
                } catch (\Throwable $e) {
                    $this->stdout("Unable to save translation for category={$category}, language={$language}.\n\n", Console::FG_RED);
                }
            }
        }

        return ExitCode::OK;
    }

    /**
     * Extracts messages from a file.
     *
     * @param string $fileName name of the file to extract messages from
     * @param string $translator name of the function used to translate messages
     * @param array $ignoreCategories message categories to ignore.
     * This parameter is available since version 2.0.4.
     * @return array
     */
    protected function extractMessages($fileName, $translator, $ignoreCategories = [])
    {
        $this->stdout('Extracting messages from ');
        $this->stdout($fileName, Console::FG_CYAN);
        $this->stdout("...\n");

        $subject = file_get_contents($fileName);
        $messages = [];
        $tokens = token_get_all($subject);
        foreach ((array) $translator as $currentTranslator) {
            $translatorTokens = token_get_all('<?php ' . $currentTranslator);
            array_shift($translatorTokens);
            $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($tokens, $translatorTokens, $ignoreCategories));
        }

        $this->stdout("\n");

        return $messages;
    }

    /**
     * Extracts messages from a parsed PHP tokens list.
     * @param array $tokens tokens to be processed.
     * @param array $translatorTokens translator tokens.
     * @param array $ignoreCategories message categories to ignore.
     * @return array messages.
     */
    protected function extractMessagesFromTokens(array $tokens, array $translatorTokens, array $ignoreCategories)
    {
        $messages = [];
        $translatorTokensCount = count($translatorTokens);
        $matchedTokensCount = 0;
        $buffer = [];
        $pendingParenthesisCount = 0;

        foreach ($tokens as $token) {
            // finding out translator call
            if ($matchedTokensCount < $translatorTokensCount) {
                if ($this->tokensEqual($token, $translatorTokens[$matchedTokensCount])) {
                    $matchedTokensCount++;
                } else {
                    $matchedTokensCount = 0;
                }
            } elseif ($matchedTokensCount === $translatorTokensCount) {
                // translator found

                // end of function call
                if ($this->tokensEqual(')', $token)) {
                    $pendingParenthesisCount--;

                    if ($pendingParenthesisCount === 0) {
                        // end of translator call or end of something that we can't extract
                        if (isset($buffer[0][0], $buffer[1], $buffer[2][0]) && $buffer[0][0] === T_CONSTANT_ENCAPSED_STRING && $buffer[1] === ',' && $buffer[2][0] === T_CONSTANT_ENCAPSED_STRING) {
                            // is valid call we can extract
                            $category = stripcslashes($buffer[0][1]);
                            $category = mb_substr($category, 1, -1);

                            if (!$this->isCategoryIgnored($category, $ignoreCategories)) {
                                $fullMessage = mb_substr($buffer[2][1], 1, -1);
                                $i = 3;
                                while ($i < count($buffer) - 1 && !is_array($buffer[$i]) && $buffer[$i] === '.') {
                                    $fullMessage .= mb_substr($buffer[$i + 1][1], 1, -1);
                                    $i += 2;
                                }

                                $message = stripcslashes($fullMessage);
                                $messages[$category][] = $message;
                            }

                            $nestedTokens = array_slice($buffer, 3);
                            if (count($nestedTokens) > $translatorTokensCount) {
                                // search for possible nested translator calls
                                $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($nestedTokens, $translatorTokens, $ignoreCategories));
                            }
                        } else {
                            // invalid call or dynamic call we can't extract
                            $line = Console::ansiFormat($this->getLine($buffer), [Console::FG_CYAN]);
                            $skipping = Console::ansiFormat('Skipping line', [Console::FG_YELLOW]);
                            $this->stdout("$skipping $line. Make sure both category and message are static strings.\n");
                        }

                        // prepare for the next match
                        $matchedTokensCount = 0;
                        $pendingParenthesisCount = 0;
                        $buffer = [];
                    } else {
                        $buffer[] = $token;
                    }
                } elseif ($this->tokensEqual('(', $token)) {
                    // count beginning of function call, skipping translator beginning
                    if ($pendingParenthesisCount > 0) {
                        $buffer[] = $token;
                    }
                    $pendingParenthesisCount++;
                } elseif (isset($token[0]) && !in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
                    // ignore comments and whitespaces
                    $buffer[] = $token;
                }
            }
        }

        return $messages;
    }

    /**
     * The method checks, whether the $category is ignored according to $ignoreCategories array.
     *
     * Examples:
     *
     * - `myapp` - will be ignored only `myapp` category;
     * - `myapp*` - will be ignored by all categories beginning with `myapp` (`myapp`, `myapplication`, `myapprove`, `myapp/widgets`, `myapp.widgets`, etc).
     *
     * @param string $category category that is checked
     * @param array $ignoreCategories message categories to ignore.
     * @return bool
     * @since 2.0.7
     */
    protected function isCategoryIgnored($category, array $ignoreCategories)
    {
        if (!empty($ignoreCategories)) {
            if (in_array($category, $ignoreCategories, true)) {
                return true;
            }
            foreach ($ignoreCategories as $pattern) {
                if (strpos($pattern, '*') > 0 && strpos($category, rtrim($pattern, '*')) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Finds out if two PHP tokens are equal.
     *
     * @param array|string $a
     * @param array|string $b
     * @return bool
     * @since 2.0.1
     */
    protected function tokensEqual($a, $b)
    {
        if (is_string($a) && is_string($b)) {
            return $a === $b;
        }
        if (isset($a[0], $a[1], $b[0], $b[1])) {
            return $a[0] === $b[0] && $a[1] == $b[1];
        }

        return false;
    }

    /**
     * Finds out a line of the first non-char PHP token found.
     *
     * @param array $tokens
     * @return int|string
     * @since 2.0.1
     */
    protected function getLine($tokens)
    {
        foreach ($tokens as $token) {
            if (isset($token[2])) {
                return $token[2];
            }
        }

        return 'unknown';
    }

    /**
     * @param string $configFile
     * @throws Exception If configuration file does not exists.
     * @since 2.0.13
     */
    protected function initConfig($configFile)
    {
        $configFileContent = [];
        if ($configFile !== null) {
            $configFile = Yii::getAlias($configFile);
            if (!is_file($configFile)) {
                throw new Exception("The configuration file does not exist: $configFile");
            }
            $configFileContent = require $configFile;
        }

        $this->config = array_merge(
            $this->getOptionValues($this->action->id),
            $configFileContent,
            $this->getPassedOptionValues()
        );
        $this->config['sourcePath'] = Yii::getAlias($this->config['sourcePath']);
        $this->config['messagePath'] = Yii::getAlias($this->config['messagePath']);

        if (!isset($this->config['sourcePath'], $this->config['languages'])) {
            throw new Exception('The configuration file must specify "sourcePath" and "languages".');
        }
        if (!is_dir($this->config['sourcePath'])) {
            throw new Exception("The source path {$this->config['sourcePath']} is not a valid directory.");
        }

        if (!empty($this->config['format'])) {
            if (!in_array($this->config['format'], ['php', 'po', 'pot', 'db'])) {
                throw new Exception('Format should be either "php", "po", "pot" or "db".');
            }
            if (in_array($this->config['format'], ['php', 'po', 'pot'])) {
                if (!isset($this->config['messagePath'])) {
                    throw new Exception('The configuration file must specify "messagePath".');
                }
                if (!is_dir($this->config['messagePath'])) {
                    throw new Exception("The message path {$this->config['messagePath']} is not a valid directory.");
                }
            }
        }
        if (empty($this->config['languages'])) {
            throw new Exception('Languages cannot be empty.');
        }

        if ($this->config['format'] === 'php' && $this->config['phpDocBlock'] === null) {
            $this->config['phpDocBlock'] = <<<DOCBLOCK
/**
 * Message translations.
 *
 * This file is automatically generated by 'yii {$this->id}/{$this->action->id}' command.
 * It contains the localizable messages extracted from source code.
 * You may modify this file by translating the extracted messages.
 *
 * Each array element represents the translation (value) of a message (key).
 * If the value is empty, the message is considered as not translated.
 * Messages that no longer need translation will have their translations
 * enclosed between a pair of '@@' marks.
 *
 * Message string can be used with plural forms format. Check i18n section
 * of the guide for details.
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
DOCBLOCK;
        }
    }
}
