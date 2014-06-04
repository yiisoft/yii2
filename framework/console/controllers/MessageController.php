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
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

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
     * Creates a configuration file for the "extract" command.
     *
     * The generated configuration file contains detailed instructions on
     * how to customize it to fit for your needs. After customization,
     * you may use this configuration file with the "extract" command.
     *
     * @param string $filePath output file name or alias.
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
        copy(Yii::getAlias('@yii/views/messageConfig.php'), $filePath);
        echo "Configuration file template created at '{$filePath}'.\n\n";
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
    public function actionExtract($configFile)
    {
        $configFile = Yii::getAlias($configFile);
        if (!is_file($configFile)) {
            throw new Exception("The configuration file does not exist: $configFile");
        }

        $config = array_merge([
            'translator' => 'Yii::t',
            'overwrite' => false,
            'removeUnused' => false,
            'sort' => false,
            'format' => 'php',
        ], require($configFile));

        if (!isset($config['sourcePath'], $config['messagePath'], $config['languages'])) {
            throw new Exception('The configuration file must specify "sourcePath", "messagePath" and "languages".');
        }
        if (!is_dir($config['sourcePath'])) {
            throw new Exception("The source path {$config['sourcePath']} is not a valid directory.");
        }
        if (in_array($config['format'], ['php', 'po'])) {
            if (!is_dir($config['messagePath'])) {
                throw new Exception("The message path {$config['messagePath']} is not a valid directory.");
            }
        }
        if (empty($config['languages'])) {
            throw new Exception("Languages cannot be empty.");
        }
        if (empty($config['format']) || !in_array($config['format'], ['php', 'po', 'db'])) {
            throw new Exception('Format should be either "php", "po" or "db".');
        }

        $files = FileHelper::findFiles(realpath($config['sourcePath']), $config);

        $messages = [];
        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $config['translator']));
        }
        if (in_array($config['format'], ['php', 'po'])) {
            foreach ($config['languages'] as $language) {
                $dir = $config['messagePath'] . DIRECTORY_SEPARATOR . $language;
                if (!is_dir($dir)) {
                    @mkdir($dir);
                }
                foreach ($messages as $category => $msgs) {
                    $file = str_replace("\\", '/', "$dir/$category." . $config['format']);
                    $path = dirname($file);
                    if (!is_dir($path)) {
                        mkdir($path, 0755, true);
                    }
                    $msgs = array_values(array_unique($msgs));
                    $this->generateMessageFile($msgs, $file, $config['overwrite'], $config['removeUnused'], $config['sort'], $config['format']);
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
                $config['languages']
            );
        }
    }

    /**
     * Saves messages to database
     *
     * @param array $messages
     * @param \yii\db\Connection $db
     * @param string $sourceMessageTable
     * @param string $messageTable
     * @param boolean $removeUnused
     * @param array $languages
     */
    protected function saveMessagesToDb($messages, $db, $sourceMessageTable, $messageTable, $removeUnused, $languages)
    {
        $q = new \yii\db\Query;
        $current = [];

        foreach ($q->select(['id', 'category', 'message'])->from($sourceMessageTable)->all() as $row) {
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
        echo "Inserting new messages...";
        $savedFlag = false;

        foreach ($new as $category => $msgs) {
            foreach ($msgs as $m) {
                $savedFlag = true;

                $db->createCommand()
                ->insert($sourceMessageTable, ['category' => $category, 'message' => $m])->execute();
                $lastId = $db->getLastInsertID();
                foreach ($languages as $language) {
                    $db->createCommand()
                    ->insert($messageTable, ['id' => $lastId, 'language' => $language])->execute();
                }
            }
        }

        echo $savedFlag ? "saved.\n" : "nothing new...skipped.\n";
        echo $removeUnused ? "Deleting obsoleted messages..." : "Updating obsoleted messages...";

        if (empty($obsolete)) {
            echo "nothing obsoleted...skipped.\n";
        } else {
            if ($removeUnused) {
                $db->createCommand()
                ->delete($sourceMessageTable, ['in', 'id', $obsolete])->execute();
                echo "deleted.\n";
            } else {
                $last_id = $db->getLastInsertID();
                $db->createCommand()
                    ->update(
                        $sourceMessageTable,
                        ['message' => new \yii\db\Expression("CONCAT('@@',message,'@@')")],
                        ['in', 'id', $obsolete]
                    )->execute();
                foreach ($languages as $language) {
                    $db->createCommand()
                    ->insert($messageTable, ['id' => $last_id, 'language' => $language])->execute();
                }
                echo "updated.\n";
            }
        }
    }

    /**
     * Extracts messages from a file
     *
     * @param string $fileName name of the file to extract messages from
     * @param string $translator name of the function used to translate messages
     * @return array
     */
    protected function extractMessages($fileName, $translator)
    {
        echo "Extracting messages from $fileName...\n";
        $subject = file_get_contents($fileName);
        $messages = [];
        if (!is_array($translator)) {
            $translator = [$translator];
        }
        foreach ($translator as $currentTranslator) {
            $n = preg_match_all(
                '/\b' . $currentTranslator . '\s*\(\s*(\'.*?(?<!\\\\)\'|".*?(?<!\\\\)")\s*,\s*(\'.*?(?<!\\\\)\'|".*?(?<!\\\\)")\s*[,\)]/s',
                $subject,
                $matches,
                PREG_SET_ORDER
            );
            for ($i = 0; $i < $n; ++$i) {
                if (($pos = strpos($matches[$i][1], '.')) !== false) {
                    $category = substr($matches[$i][1], $pos + 1, -1);
                } else {
                    $category = substr($matches[$i][1], 1, -1);
                }
                $message = $matches[$i][2];
                $messages[$category][] = eval("return $message;"); // use eval to eliminate quote escape
            }
        }

        return $messages;
    }

    /**
     * Writes messages into file
     *
     * @param array $messages
     * @param string $fileName name of the file to write to
     * @param boolean $overwrite if existing file should be overwritten without backup
     * @param boolean $removeUnused if obsolete translations should be removed
     * @param boolean $sort if translations should be sorted
     * @param string $format output format
     */
    protected function generateMessageFile($messages, $fileName, $overwrite, $removeUnused, $sort, $format)
    {
        echo "Saving messages to $fileName...";
        if (is_file($fileName)) {
            if ($format === 'po') {
                $translated = file_get_contents($fileName);
                preg_match_all('/(?<=msgid ").*(?="\n(#*)msgstr)/', $translated, $keys);
                preg_match_all('/(?<=msgstr ").*(?="\n\n)/', $translated, $values);
                $translated = array_combine($keys[0], $values[0]);
            } else {
                $translated = require($fileName);
            }
            sort($messages);
            ksort($translated);
            if (array_keys($translated) == $messages) {
                echo "nothing new...skipped.\n";

                return self::EXIT_CODE_NORMAL;
            }
            $merged = [];
            $untranslated = [];
            foreach ($messages as $message) {
                if ($format === 'po') {
                    $message = preg_replace('/\"/', '\"', $message);
                }
                if (array_key_exists($message, $translated) && strlen($translated[$message]) > 0) {
                    $merged[$message] = $translated[$message];
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
            ksort($translated);
            foreach ($translated as $message => $translation) {
                if (!isset($merged[$message]) && !isset($todo[$message]) && !$removeUnused) {
                    if (substr($translation, 0, 2) === '@@' && substr($translation, -2) === '@@') {
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
            if ($format === 'po') {
                $output = '';
                foreach ($merged as $k => $v) {
                    $k = preg_replace('/(\")|(\\\")/', "\\\"", $k);
                    $v = preg_replace('/(\")|(\\\")/', "\\\"", $v);
                    if (substr($v, 0, 2) === '@@' && substr($v, -2) === '@@') {
                        $output .= "#msgid \"$k\"\n";
                        $output .= "#msgstr \"$v\"\n";
                    } else {
                        $output .= "msgid \"$k\"\n";
                        $output .= "msgstr \"$v\"\n";
                    }
                    $output .= "\n";
                }
                $merged = $output;
            }
            echo "translation merged.\n";
        } else {
            if ($format === 'po') {
                $merged = '';
                sort($messages);
                foreach ($messages as $message) {
                    $message = preg_replace('/(\")|(\\\")/', '\\\"', $message);
                    $merged .= "msgid \"$message\"\n";
                    $merged .= "msgstr \"\"\n";
                    $merged .= "\n";
                }
            } else {
                $merged = [];
                foreach ($messages as $message) {
                    $merged[$message] = '';
                }
                ksort($merged);
            }
            echo "saved.\n";
        }
        if ($format === 'po') {
            $content = $merged;
        } else {
            $array = VarDumper::export($merged);
            $content = <<<EOD
<?php
/**
 * Message translations.
 *
 * This file is automatically generated by 'yii {$this->id}' command.
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
        }
        file_put_contents($fileName, $content);
    }
}
