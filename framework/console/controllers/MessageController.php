<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\i18n\GettextPoFile;

/**
 * 从源文件中提取要翻译的消息。
 *
 * 根据配置文件中的 `format` 设置，提取的消息可以
 * 保存如下：
 *
 * - PHP 消息源文件。
 * - ".po" 文件。
 * - 数据库。
 *
 * 用法：
 * 1. 通过 'message/config' 命令创建配置文件：
 *    yii message/config /path/to/myapp/messages/config.php
 * 2. 编辑创建的配置文件，根据 Web 应用程序的需要对其进行调整。
 * 3. 运行 'message/extract' 命令，使用创建的配置：
 *    yii message /path/to/myapp/messages/config.php
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MessageController extends \yii\console\Controller
{
    /**
     * @var string 控制器默认动作 ID。
     */
    public $defaultAction = 'extract';
    /**
     * @var string 必须，所有源文件的根目录。
     */
    public $sourcePath = '@yii';
    /**
     * @var string 必须，包含消息转换的根目录。
     */
    public $messagePath = '@yii/messages';
    /**
     * @var array 必须，将提取的消息转换为的语言代码列表。
     * 例如，['zh-CN', 'de']。
     */
    public $languages = [];
    /**
     * @var string 用于翻译消息的函数的名称。
     * 默认为 'Yii::t'。此标记用作查找要翻译的消息的标记。
     * 你可以对单个函数名使用字符串，
     * 或对多个函数名使用数组。
     */
    public $translator = 'Yii::t';
    /**
     * @var bool 是否在将新消息与现有消息合并时按键对消息进行排序。
     * 默认为 false，也就是说新的（untranslated）
     * 消息将从旧的（translated）消息中分离出来。
     */
    public $sort = false;
    /**
     * @var bool 是否应用合并的消息覆盖消息文件
     */
    public $overwrite = true;
    /**
     * @var bool 是否删除不再出现在源代码中的消息。
     * 默认值为 false，这意味着这些消息将不会被删除。
     */
    public $removeUnused = false;
    /**
     * @var bool 是否标记不再出现在源代码中的消息。
     * 默认值为 true，这意味着这些消息中的每一条都将包含一对 '@@' 标记。
     */
    public $markUnused = true;
    /**
     * @var array 指定不应处理哪些文件/目录的模式列表。
     * 如果为空或未设置，将处理所有文件/目录。
     * 查看 helpers/FileHelper::findFiles() 模式匹配规则的说明。
     * 如果文件/目录与 "only" 和 "except" 中的模式都匹配，则不会对其进行处理。
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
     * @var array 指定应处理哪些文件(而不是目录)的模式列表。
     * 如果为空或未设置，则将处理所有文件。
     * 查看 helpers/FileHelper::findFiles() 模式匹配规则的说明。
     * 如果文件/目录与 "only" 和 "except"，则不会对其进行处理。
     */
    public $only = ['*.php'];
    /**
     * @var string 生成的文件格式。可以是 "php"，"db"，"po" 或者 "pot"。
     */
    public $format = 'php';
    /**
     * @var string "db" 格式的连接组件 ID。
     */
    public $db = 'db';
    /**
     * @var string "db" 格式的源消息表的自定义名称。
     */
    public $sourceMessageTable = '{{%source_message}}';
    /**
     * @var string "db" 格式的转换消息表的自定义名称。
     */
    public $messageTable = '{{%message}}';
    /**
     * @var string 将用于翻译 "po" 格式的文件的名称。
     */
    public $catalog = 'messages';
    /**
     * @var array 要忽略的消息类别。例如 'yii'，'app*'，'widgets/menu'，等。
     * @see isCategoryIgnored
     */
    public $ignoreCategories = [];
    /**
     * @var string 生成的带有消息的 PHP 文件中的文件头。此属性仅用于如果 [[$format]] 是 "php"。
     * @since 2.0.13
     */
    public $phpFileHeader = '';
    /**
     * @var string|null 在生成的 PHP 文件中用于消息数组的 DocBlock。如果是 `null`，将使用默认的 DocBlock。
     * 此属性仅用于如果 [[$format]] 是 "php"。
     * @since 2.0.13
     */
    public $phpDocBlock;

    /**
     * @var array 用于消息提取的配置。
     * @see actionExtract()
     * @see initConfig()
     * @since 2.0.13
     */
    protected $config;


    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * 使用指定的命令行选项为 "extract" 命令创建配置文件。
     *
     * 生成的配置文件包含源代码消息
     * 提取所需的参数。
     * 你可以将此配置文件与 "extract" 命令一起使用。
     *
     * @param string $filePath 输出文件名或别名。
     * @return int CLI 退出代码
     * @throws Exception 失败的时候。
     */
    public function actionConfig($filePath)
    {
        $filePath = Yii::getAlias($filePath);
        $dir = dirname($filePath);

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

        if (FileHelper::createDirectory($dir) === false || file_put_contents($filePath, $content, LOCK_EX) === false) {
            $this->stdout("Configuration file was NOT created: '{$filePath}'.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Configuration file created: '{$filePath}'.\n\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * 为 "extract" 命令创建配置文件模板。
     *
     * 创建的配置文件包含有关
     * 如何根据您的需要对其进行自定义的详细说明。定制后，
     * 您可以将此配置文件与 "extract" 命令配合使用。
     *
     * @param string $filePath 输出文件名或别名。
     * @return int CLI 退出代码
     * @throws Exception 失败的时候。
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
     * 从源代码中提取要翻译的消息。
     *
     * 此命令将搜索源代码文件
     * 并提取需要翻译为不同语言的消息。
     *
     * @param string $configFile 配置文件的路径或别名。
     * 你可以使用 "yii message/config" 命令以生成
     * 此文件，然后根据您的需要对其进行自定义。
     * @throws Exception 失败的时候。
     */
    public function actionExtract($configFile = null)
    {
        $this->initConfig($configFile);

        $files = FileHelper::findFiles(realpath($this->config['sourcePath']), $this->config);

        $messages = [];
        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $this->config['translator'], $this->config['ignoreCategories']));
        }

        $catalog = isset($this->config['catalog']) ? $this->config['catalog'] : 'messages';

        if (in_array($this->config['format'], ['php', 'po'])) {
            foreach ($this->config['languages'] as $language) {
                $dir = $this->config['messagePath'] . DIRECTORY_SEPARATOR . $language;
                if (!is_dir($dir) && !@mkdir($dir)) {
                    throw new Exception("Directory '{$dir}' can not be created.");
                }
                if ($this->config['format'] === 'po') {
                    $this->saveMessagesToPO($messages, $dir, $this->config['overwrite'], $this->config['removeUnused'], $this->config['sort'], $catalog, $this->config['markUnused']);
                } else {
                    $this->saveMessagesToPHP($messages, $dir, $this->config['overwrite'], $this->config['removeUnused'], $this->config['sort'], $this->config['markUnused']);
                }
            }
        } elseif ($this->config['format'] === 'db') {
            /** @var Connection $db */
            $db = Instance::ensure($this->config['db'], Connection::className());
            $sourceMessageTable = isset($this->config['sourceMessageTable']) ? $this->config['sourceMessageTable'] : '{{%source_message}}';
            $messageTable = isset($this->config['messageTable']) ? $this->config['messageTable'] : '{{%message}}';
            $this->saveMessagesToDb(
                $messages,
                $db,
                $sourceMessageTable,
                $messageTable,
                $this->config['removeUnused'],
                $this->config['languages'],
                $this->config['markUnused']
            );
        } elseif ($this->config['format'] === 'pot') {
            $this->saveMessagesToPOT($messages, $this->config['messagePath'], $catalog);
        }
    }

    /**
     * 将消息保存到数据库。
     *
     * @param array $messages
     * @param Connection $db
     * @param string $sourceMessageTable
     * @param string $messageTable
     * @param bool $removeUnused
     * @param array $languages
     * @param bool $markUnused
     */
    protected function saveMessagesToDb($messages, $db, $sourceMessageTable, $messageTable, $removeUnused, $languages, $markUnused)
    {
        $currentMessages = [];
        $rows = (new Query())->select(['id', 'category', 'message'])->from($sourceMessageTable)->all($db);
        foreach ($rows as $row) {
            $currentMessages[$row['category']][$row['id']] = $row['message'];
        }

        $currentLanguages = [];
        $rows = (new Query())->select(['language'])->from($messageTable)->groupBy('language')->all($db);
        foreach ($rows as $row) {
            $currentLanguages[] = $row['language'];
        }
        $missingLanguages = [];
        if (!empty($currentLanguages)) {
            $missingLanguages = array_diff($languages, $currentLanguages);
        }

        $new = [];
        $obsolete = [];

        foreach ($messages as $category => $msgs) {
            $msgs = array_unique($msgs);

            if (isset($currentMessages[$category])) {
                $new[$category] = array_diff($msgs, $currentMessages[$category]);
                $obsolete += array_diff($currentMessages[$category], $msgs);
            } else {
                $new[$category] = $msgs;
            }
        }

        foreach (array_diff(array_keys($currentMessages), array_keys($messages)) as $category) {
            $obsolete += $currentMessages[$category];
        }

        if (!$removeUnused) {
            foreach ($obsolete as $pk => $msg) {
                if (mb_substr($msg, 0, 2) === '@@' && mb_substr($msg, -2) === '@@') {
                    unset($obsolete[$pk]);
                }
            }
        }

        $obsolete = array_keys($obsolete);
        $this->stdout('Inserting new messages...');
        $savedFlag = false;

        foreach ($new as $category => $msgs) {
            foreach ($msgs as $msg) {
                $savedFlag = true;
                $lastPk = $db->schema->insert($sourceMessageTable, ['category' => $category, 'message' => $msg]);
                foreach ($languages as $language) {
                    $db->createCommand()
                       ->insert($messageTable, ['id' => $lastPk['id'], 'language' => $language])
                       ->execute();
                }
            }
        }

        if (!empty($missingLanguages)) {
            $updatedMessages = [];
            $rows = (new Query())->select(['id', 'category', 'message'])->from($sourceMessageTable)->all($db);
            foreach ($rows as $row) {
                $updatedMessages[$row['category']][$row['id']] = $row['message'];
            }
            foreach ($updatedMessages as $category => $msgs) {
                foreach ($msgs as $id => $msg) {
                    $savedFlag = true;
                    foreach ($missingLanguages as $language) {
                        $db->createCommand()
                            ->insert($messageTable, ['id' => $id, 'language' => $language])
                            ->execute();
                    }
                }
            }
        }

        $this->stdout($savedFlag ? "saved.\n" : "Nothing to save.\n");
        $this->stdout($removeUnused ? 'Deleting obsoleted messages...' : 'Updating obsoleted messages...');

        if (empty($obsolete)) {
            $this->stdout("Nothing obsoleted...skipped.\n");
            return;
        }

        if ($removeUnused) {
            $db->createCommand()
               ->delete($sourceMessageTable, ['in', 'id', $obsolete])
               ->execute();
            $this->stdout("deleted.\n");
        } elseif ($markUnused) {
            $rows = (new Query())
                ->select(['id', 'message'])
                ->from($sourceMessageTable)
                ->where(['in', 'id', $obsolete])
                ->all($db);

            foreach ($rows as $row) {
                $db->createCommand()->update(
                    $sourceMessageTable,
                    ['message' => '@@' . $row['message'] . '@@'],
                    ['id' => $row['id']]
                )->execute();
            }
            $this->stdout("updated.\n");
        } else {
            $this->stdout("kept untouched.\n");
        }
    }

    /**
     * 从文件中提取消息。
     *
     * @param string $fileName 要从中提取消息的文件的名称。
     * @param string $translator 用于翻译消息的函数的名称。
     * @param array $ignoreCategories 要忽略的消息类别。
     * 此参数自版本 2.0.4。起可用
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
     * 从解析的 PHP 令牌列表中提取消息。
     * @param array $tokens 要处理的令牌。
     * @param array $translatorTokens 翻译令牌。
     * @param array $ignoreCategories 要忽略的消息类别。
     * @return array messages。
     */
    protected function extractMessagesFromTokens(array $tokens, array $translatorTokens, array $ignoreCategories)
    {
        $messages = [];
        $translatorTokensCount = count($translatorTokens);
        $matchedTokensCount = 0;
        $buffer = [];
        $pendingParenthesisCount = 0;

        foreach ($tokens as $i => $token) {
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
                    // Ensure that it's not the call of the object method. See https://github.com/yiisoft/yii2/issues/16828
                    $previousTokenId = $tokens[$i - $matchedTokensCount - 1][0];
                    if (in_array($previousTokenId, [T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM], true)) {
                        $matchedTokensCount = 0;
                        continue;
                    }

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
     * 方法检查，$category 是否被忽略根据 $ignoreCategories 数组。
     *
     * 例如：
     *
     * - `myapp` - 将仅被忽略 `myapp` 类别；
     * - `myapp*` - 将被忽略所有以 `myapp`（`myapp`，`myapplication`，`myapprove`，`myapp/widgets`，`myapp.widgets`，等）开头的类别。
     *
     * @param string $category 选中的类别
     * @param array $ignoreCategories 要忽略的消息类别。
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
     * 查找两个 PHP 标记是否相等。
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
     * 查找找到的第一个 non-char PHP 标记的一行。
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
     * 将消息写入 PHP 文件。
     *
     * @param array $messages
     * @param string $dirName 要写入的目录的名称
     * @param bool $overwrite 如果在没有备份的情况下覆盖现有文件
     * @param bool $removeUnused 如果应删除过时的译文
     * @param bool $sort 如果翻译需要排序
     * @param bool $markUnused 如果应标记过时的译文
     */
    protected function saveMessagesToPHP($messages, $dirName, $overwrite, $removeUnused, $sort, $markUnused)
    {
        foreach ($messages as $category => $msgs) {
            $file = str_replace('\\', '/', "$dirName/$category.php");
            $path = dirname($file);
            FileHelper::createDirectory($path);
            $msgs = array_values(array_unique($msgs));
            $coloredFileName = Console::ansiFormat($file, [Console::FG_CYAN]);
            $this->stdout("Saving messages to $coloredFileName...\n");
            $this->saveMessagesCategoryToPHP($msgs, $file, $overwrite, $removeUnused, $sort, $category, $markUnused);
        }

        if ($removeUnused) {
            $this->deleteUnusedPhpMessageFiles($dirName, array_keys($messages));
        }
    }

    /**
     * 将类别消息写入 PHP 文件。
     *
     * @param array $messages
     * @param string $fileName 要写入的文件的名称
     * @param bool $overwrite 如果在没有备份的情况下覆盖现有文件
     * @param bool $removeUnused 如果应删除过时的译文
     * @param bool $sort 如果翻译需要排序
     * @param string $category 消息类别
     * @param bool $markUnused 如果应标记过时的译文
     * @return int 退出代码
     */
    protected function saveMessagesCategoryToPHP($messages, $fileName, $overwrite, $removeUnused, $sort, $category, $markUnused)
    {
        if (is_file($fileName)) {
            $rawExistingMessages = require $fileName;
            $existingMessages = $rawExistingMessages;
            sort($messages);
            ksort($existingMessages);
            if (array_keys($existingMessages) === $messages && (!$sort || array_keys($rawExistingMessages) === $messages)) {
                $this->stdout("Nothing new in \"$category\" category... Nothing to save.\n\n", Console::FG_GREEN);
                return ExitCode::OK;
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
                    if (!$markUnused || (!empty($translation) && (strncmp($translation, '@@', 2) === 0 && substr_compare($translation, '@@', -2, 2) === 0))) {
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
{$this->config['phpFileHeader']}{$this->config['phpDocBlock']}
return $array;

EOD;

        if (file_put_contents($fileName, $content, LOCK_EX) === false) {
            $this->stdout("Translation was NOT saved.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Translation saved.\n\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * 将消息写入 PO 文件。
     *
     * @param array $messages
     * @param string $dirName 要写入的目录的名称
     * @param bool $overwrite 如果在没有备份的情况下覆盖现有文件
     * @param bool $removeUnused 如果应删除过时的译文
     * @param bool $sort 如果翻译需要排序
     * @param string $catalog 消息目录
     * @param bool $markUnused 如果应标记过时的译文
     */
    protected function saveMessagesToPO($messages, $dirName, $overwrite, $removeUnused, $sort, $catalog, $markUnused)
    {
        $file = str_replace('\\', '/', "$dirName/$catalog.po");
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
                        if (!$markUnused || (!empty($translation) && (substr($translation, 0, 2) === '@@' && substr($translation, -2) === '@@'))) {
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
     * 将消息写入 POT 文件。
     *
     * @param array $messages
     * @param string $dirName 要写入的目录的名称
     * @param string $catalog 消息目录
     * @since 2.0.6
     */
    protected function saveMessagesToPOT($messages, $dirName, $catalog)
    {
        $file = str_replace('\\', '/', "$dirName/$catalog.pot");
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

    private function deleteUnusedPhpMessageFiles($dirName, $existingCategories)
    {
        $messageFiles = FileHelper::findFiles($dirName);
        foreach ($messageFiles as $file) {
            $category = preg_replace('#\.php$#', '', basename($file));
            if (!in_array($category, $existingCategories, true)) {
                unlink($file);
            }
        }
    }

    /**
     * @param string $configFile
     * @throws Exception 如果配置文件不存在。
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
        if (empty($this->config['format']) || !in_array($this->config['format'], ['php', 'po', 'pot', 'db'])) {
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
