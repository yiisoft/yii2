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
use yii\helpers\VarDumper;
use yii\i18n\GettextPoFile;

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
class MessageController extends Controller
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
     * @var string generated file format. Can be "php", "db" or "po".
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
     * Creates a configuration file for the "extract" command using command line options specified
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
                return self::EXIT_CODE_NORMAL;
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

        if (file_put_contents($filePath, $content) !== false) {
            $this->stdout("Configuration file created: '{$filePath}'.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        } else {
            $this->stdout("Configuration file was NOT created: '{$filePath}'.\n\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
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
                return self::EXIT_CODE_NORMAL;
            }
        }
        if (copy(Yii::getAlias('@yii/views/messageConfig.php'), $filePath)) {
            $this->stdout("Configuration file template created at '{$filePath}'.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        } else {
            $this->stdout("Configuration file template was NOT created at '{$filePath}'.\n\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
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
     * @throws Exception on failure.
     */
    public function actionExtract($configFile = null)
    {
        $configFileContent = [];
        if ($configFile !== null) {
            $configFile = Yii::getAlias($configFile);
            if (!is_file($configFile)) {
                throw new Exception("The configuration file does not exist: $configFile");
            } else {
                $configFileContent = require($configFile);
            }
        }

        $config = array_merge(
            $this->getOptionValues($this->action->id),
            $configFileContent,
            $this->getPassedOptionValues()
        );
        $config['sourcePath'] = Yii::getAlias($config['sourcePath']);
        $config['messagePath'] = Yii::getAlias($config['messagePath']);

        if (!isset($config['sourcePath'], $config['languages'])) {
            throw new Exception('The configuration file must specify "sourcePath" and "languages".');
        }
        if (!is_dir($config['sourcePath'])) {
            throw new Exception("The source path {$config['sourcePath']} is not a valid directory.");
        }
        if (empty($config['format']) || !in_array($config['format'], ['php', 'po', 'pot', 'db'])) {
            throw new Exception('Format should be either "php", "po", "pot" or "db".');
        }
        if (in_array($config['format'], ['php', 'po', 'pot'])) {
            if (!isset($config['messagePath'])) {
                throw new Exception('The configuration file must specify "messagePath".');
            } elseif (!is_dir($config['messagePath'])) {
                throw new Exception("The message path {$config['messagePath']} is not a valid directory.");
            }
        }
        if (empty($config['languages'])) {
            throw new Exception('Languages cannot be empty.');
        }

        $files = FileHelper::findFiles(realpath($config['sourcePath']), $config);

        $messages = [];
        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $config['translator'], $config['ignoreCategories']));
        }
        if (in_array($config['format'], ['php', 'po'])) {
            foreach ($config['languages'] as $language) {
                $dir = $config['messagePath'] . DIRECTORY_SEPARATOR . $language;
                if (!is_dir($dir)) {
                    @mkdir($dir);
                }
                if ($config['format'] === 'po') {
                    $catalog = isset($config['catalog']) ? $config['catalog'] : 'messages';
                    $this->saveMessagesToPO($messages, $dir, $config['overwrite'], $config['removeUnused'], $config['sort'], $catalog, $config['markUnused']);
                } else {
                    $this->saveMessagesToPHP($messages, $dir, $config['overwrite'], $config['removeUnused'], $config['sort'], $config['markUnused']);
                }
            }
        } elseif ($config['format'] === 'db') {
            $db = \Yii::$app->get(isset($config['db']) ? $config['db'] : 'db');
            if (!$db instanceof \yii\db\Connection) {
                throw new Exception('The "db" option must refer to a valid database application component.');
            }
            $sourceMessageTable = isset($config['sourceMessageTable']) ? $config['sourceMessageTable'] : '{{%source_message}}';
            $messageTable = isset($config['messageTable']) ? $config['messageTable'] : '{{%message}}';
            $this->saveMessagesToDb(
                $messages,
                $db,
                $sourceMessageTable,
                $messageTable,
                $config['removeUnused'],
                $config['languages'],
                $config['markUnused']
            );
        } elseif ($config['format'] === 'pot') {
            $catalog = isset($config['catalog']) ? $config['catalog'] : 'messages';
            $this->saveMessagesToPOT($messages, $config['messagePath'], $catalog);
        }
    }

    /**
     * Saves messages to database
     *
     * @param array $messages
     * @param \yii\db\Connection $db
     * @param string $sourceMessageTable
     * @param string $messageTable
     * @param bool $removeUnused
     * @param array $languages
     * @param bool $markUnused
     */
    protected function saveMessagesToDb($messages, $db, $sourceMessageTable, $messageTable, $removeUnused, $languages, $markUnused)
    {
        $q = new \yii\db\Query;
        $current = [];

        foreach ($q->select(['id', 'category', 'message'])->from($sourceMessageTable)->all($db) as $row) {
            $current[$row['category']][$row['id']] = $row['message'];
        }

        $new = [];
        $obsolete = [];

        foreach ($messages as $category => $msgs) {
            $msgs = array_unique($msgs);

            if (isset($current[$category])) {
                $new[$category] = array_diff($msgs, $current[$category]);
                $obsolete += array_diff($current[$category], $msgs);
            } else {
                $new[$category] = $msgs;
            }
        }

        foreach (array_diff(array_keys($current), array_keys($messages)) as $category) {
            $obsolete += $current[$category];
        }

        if (!$removeUnused) {
            foreach ($obsolete as $pk => $m) {
                if (mb_substr($m, 0, 2) === '@@' && mb_substr($m, -2) === '@@') {
                    unset($obsolete[$pk]);
                }
            }
        }

        $obsolete = array_keys($obsolete);
        $this->stdout('Inserting new messages...');
        $savedFlag = false;

        foreach ($new as $category => $msgs) {
            foreach ($msgs as $m) {
                $savedFlag = true;
                $lastPk = $db->schema->insert($sourceMessageTable, ['category' => $category, 'message' => $m]);
                foreach ($languages as $language) {
                    $db->createCommand()
                       ->insert($messageTable, ['id' => $lastPk['id'], 'language' => $language])
                       ->execute();
                }
            }
        }

        $this->stdout($savedFlag ? "saved.\n" : "Nothing new...skipped.\n");
        $this->stdout($removeUnused ? 'Deleting obsoleted messages...' : 'Updating obsoleted messages...');

        if (empty($obsolete)) {
            $this->stdout("Nothing obsoleted...skipped.\n");
        } else {
            if ($removeUnused) {
                $db->createCommand()
                   ->delete($sourceMessageTable, ['in', 'id', $obsolete])
                   ->execute();
                $this->stdout("deleted.\n");
            } elseif ($markUnused) {
                $db->createCommand()
                   ->update(
                       $sourceMessageTable,
                       ['message' => new \yii\db\Expression("CONCAT('@@',message,'@@')")],
                       ['in', 'id', $obsolete]
                   )->execute();
                $this->stdout("updated.\n");
            } else {
                $this->stdout("kept untouched.\n");
            }
        }
    }

    /**
     * Extracts messages from a file
     *
     * @param string $fileName name of the file to extract messages from
     * @param string $translator name of the function used to translate messages
     * @param array $ignoreCategories message categories to ignore.
     * This parameter is available since version 2.0.4.
     * @return array
     */
    protected function extractMessages($fileName, $translator, $ignoreCategories = [])
    {
        $coloredFileName = Console::ansiFormat($fileName, [Console::FG_CYAN]);
        $this->stdout("Extracting messages from $coloredFileName...\n");

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
    private function extractMessagesFromTokens(array $tokens, array $translatorTokens, array $ignoreCategories)
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
                            $category = mb_substr($category, 1, mb_strlen($category) - 2);

                            if (!$this->isCategoryIgnored($category, $ignoreCategories)) {
                                $message = stripcslashes($buffer[2][1]);
                                $message = mb_substr($message, 1, mb_strlen($message) - 2);

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
        $result = false;

        if (!empty($ignoreCategories)) {
            if (in_array($category, $ignoreCategories, true)) {
                $result = true;
            } else {
                foreach ($ignoreCategories as $pattern) {
                    if (strpos($pattern, '*') > 0 && strpos($category, rtrim($pattern, '*')) === 0) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Finds out if two PHP tokens are equal
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
        } elseif (isset($a[0], $a[1], $b[0], $b[1])) {
            return $a[0] === $b[0] && $a[1] == $b[1];
        }
        return false;
    }

    /**
     * Finds out a line of the first non-char PHP token found
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
     * Writes messages into PHP files
     *
     * @param array $messages
     * @param string $dirName name of the directory to write to
     * @param bool $overwrite if existing file should be overwritten without backup
     * @param bool $removeUnused if obsolete translations should be removed
     * @param bool $sort if translations should be sorted
     * @param bool $markUnused if obsolete translations should be marked
     */
    protected function saveMessagesToPHP($messages, $dirName, $overwrite, $removeUnused, $sort, $markUnused)
    {
        foreach ($messages as $category => $msgs) {
            $file = str_replace("\\", '/', "$dirName/$category.php");
            $path = dirname($file);
            FileHelper::createDirectory($path);
            $msgs = array_values(array_unique($msgs));
            $coloredFileName = Console::ansiFormat($file, [Console::FG_CYAN]);
            $this->stdout("Saving messages to $coloredFileName...\n");
            $this->saveMessagesCategoryToPHP($msgs, $file, $overwrite, $removeUnused, $sort, $category, $markUnused);
        }
    }

    /**
     * Writes category messages into PHP file
     *
     * @param array $messages
     * @param string $fileName name of the file to write to
     * @param bool $overwrite if existing file should be overwritten without backup
     * @param bool $removeUnused if obsolete translations should be removed
     * @param bool $sort if translations should be sorted
     * @param string $category message category
     * @param bool $markUnused if obsolete translations should be marked
     * @return int exit code
     */
    protected function saveMessagesCategoryToPHP($messages, $fileName, $overwrite, $removeUnused, $sort, $category, $markUnused)
    {
        if (is_file($fileName)) {
            $rawExistingMessages = require($fileName);
            $existingMessages = $rawExistingMessages;
            sort($messages);
            ksort($existingMessages);
            if (array_keys($existingMessages) === $messages && (!$sort || array_keys($rawExistingMessages) === $messages)) {
                $this->stdout("Nothing new in \"$category\" category... Nothing to save.\n\n", Console::FG_GREEN);
                return self::EXIT_CODE_NORMAL;
            }
            unset($rawExistingMessages);
            $merged = [];
            $untranslated = [];
            foreach ($messages as $message) {
                if (array_key_exists($message, $existingMessages) && $existingMessages[$message] !== '') {
                    $merged[$message] = $existingMessages[$message];
                } else {
                    $untranslated[] = $message;
                }
            }
            ksort($merged);
            sort($untranslated);
            $todo = [];
            foreach ($untranslated as $message) {
                $todo[$message] = '';
            }
            ksort($existingMessages);
            foreach ($existingMessages as $message => $translation) {
                if (!$removeUnused && !isset($merged[$message]) && !isset($todo[$message])) {
                    if (!empty($translation) && (!$markUnused || (strncmp($translation, '@@', 2) === 0 && substr_compare($translation, '@@', -2, 2) === 0))) {
                        $todo[$message] = $translation;
                    } else {
                        $todo[$message] = '@@' . $translation . '@@';
                    }
                }
            }
            $merged = array_merge($todo, $merged);
            if ($sort) {
                ksort($merged);
            }
            if (false === $overwrite) {
                $fileName .= '.merged';
            }
            $this->stdout("Translation merged.\n");
        } else {
            $merged = [];
            foreach ($messages as $message) {
                $merged[$message] = '';
            }
            ksort($merged);
        }


        $array = VarDumper::export($merged);
        $content = <<<EOD
<?php
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
return $array;

EOD;

        if (file_put_contents($fileName, $content) !== false) {
            $this->stdout("Translation saved.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        } else {
            $this->stdout("Translation was NOT saved.\n\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
    }

    /**
     * Writes messages into PO file
     *
     * @param array $messages
     * @param string $dirName name of the directory to write to
     * @param bool $overwrite if existing file should be overwritten without backup
     * @param bool $removeUnused if obsolete translations should be removed
     * @param bool $sort if translations should be sorted
     * @param string $catalog message catalog
     * @param bool $markUnused if obsolete translations should be marked
     */
    protected function saveMessagesToPO($messages, $dirName, $overwrite, $removeUnused, $sort, $catalog, $markUnused)
    {
        $file = str_replace("\\", '/', "$dirName/$catalog.po");
        FileHelper::createDirectory(dirname($file));
        $this->stdout("Saving messages to $file...\n");

        $poFile = new GettextPoFile();


        $merged = [];
        $todos = [];

        $hasSomethingToWrite = false;
        foreach ($messages as $category => $msgs) {
            $notTranslatedYet = [];
            $msgs = array_values(array_unique($msgs));

            if (is_file($file)) {
                $existingMessages = $poFile->load($file, $category);

                sort($msgs);
                ksort($existingMessages);
                if (array_keys($existingMessages) == $msgs) {
                    $this->stdout("Nothing new in \"$category\" category...\n");

                    sort($msgs);
                    foreach ($msgs as $message) {
                        $merged[$category . chr(4) . $message] = $existingMessages[$message];
                    }
                    ksort($merged);
                    continue;
                }

                // merge existing message translations with new message translations
                foreach ($msgs as $message) {
                    if (array_key_exists($message, $existingMessages) && $existingMessages[$message] !== '') {
                        $merged[$category . chr(4) . $message] = $existingMessages[$message];
                    } else {
                        $notTranslatedYet[] = $message;
                    }
                }
                ksort($merged);
                sort($notTranslatedYet);

                // collect not yet translated messages
                foreach ($notTranslatedYet as $message) {
                    $todos[$category . chr(4) . $message] = '';
                }

                // add obsolete unused messages
                foreach ($existingMessages as $message => $translation) {
                    if (!$removeUnused && !isset($merged[$category . chr(4) . $message]) && !isset($todos[$category . chr(4) . $message])) {
                        if (!empty($translation) && (!$markUnused || (substr($translation, 0, 2) === '@@' && substr($translation, -2) === '@@'))) {
                            $todos[$category . chr(4) . $message] = $translation;
                        } else {
                            $todos[$category . chr(4) . $message] = '@@' . $translation . '@@';
                        }
                    }
                }

                $merged = array_merge($todos, $merged);
                if ($sort) {
                    ksort($merged);
                }

                if ($overwrite === false) {
                    $file .= '.merged';
                }
            } else {
                sort($msgs);
                foreach ($msgs as $message) {
                    $merged[$category . chr(4) . $message] = '';
                }
                ksort($merged);
            }
            $this->stdout("Category \"$category\" merged.\n");
            $hasSomethingToWrite = true;
        }
        if ($hasSomethingToWrite) {
            $poFile->save($file, $merged);
            $this->stdout("Translation saved.\n", Console::FG_GREEN);
        } else {
            $this->stdout("Nothing to save.\n", Console::FG_GREEN);
        }
    }

    /**
     * Writes messages into POT file
     *
     * @param array $messages
     * @param string $dirName name of the directory to write to
     * @param string $catalog message catalog
     * @since 2.0.6
     */
    protected function saveMessagesToPOT($messages, $dirName, $catalog)
    {
        $file = str_replace("\\", '/', "$dirName/$catalog.pot");
        FileHelper::createDirectory(dirname($file));
        $this->stdout("Saving messages to $file...\n");

        $poFile = new GettextPoFile();

        $merged = [];

        $hasSomethingToWrite = false;
        foreach ($messages as $category => $msgs) {
            $msgs = array_values(array_unique($msgs));

            sort($msgs);
            foreach ($msgs as $message) {
                $merged[$category . chr(4) . $message] = '';
            }
            $this->stdout("Category \"$category\" merged.\n");
            $hasSomethingToWrite = true;
        }
        if ($hasSomethingToWrite) {
            ksort($merged);
            $poFile->save($file, $merged);
            $this->stdout("Translation saved.\n", Console::FG_GREEN);
        } else {
            $this->stdout("Nothing to save.\n", Console::FG_GREEN);
        }
    }
}
