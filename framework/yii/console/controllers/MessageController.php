<?php
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\FileHelper;

/**
 * This command extracts messages to be translated from source files.
 * The extracted messages are saved as PHP message source files
 * under the specified directory.
 *
 * Usage:
 * 1. Create a configuration file using the 'message/config' command:
 *    yii message/config /path/to/myapp/messages/config.php
 * 2. Edit the created config file, adjusting it for your web application needs.
 * 3. Run the 'message/extract' extract, using created config:
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
	 * @param string $filePath output file name.
	 * @throws Exception on failure.
	 */
	public function actionConfig($filePath)
	{
		if (file_exists($filePath)) {
			if (!$this->confirm("File '{$filePath}' already exists. Do you wish to overwrite it?")) {
				return;
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
	 * @param string $configFile the path of the configuration file.
	 * You may use the "yii message/config" command to generate
	 * this file and then customize it for your needs.
	 * @throws Exception on failure.
	 */
	public function actionExtract($configFile)
	{
		if (!is_file($configFile)) {
			throw new Exception("the configuration file {$configFile} does not exist.");
		}

		$config = array_merge(array(
			'translator' => 'Yii::t',
			'overwrite' => false,
			'removeUnused' => false,
			'sort' => false,
		), require($configFile));

		if (!isset($config['sourcePath'], $config['messagePath'], $config['languages'])) {
			throw new Exception('The configuration file must specify "sourcePath", "messagePath" and "languages".');
		}
		if (!is_dir($config['sourcePath'])) {
			throw new Exception("The source path {$config['sourcePath']} is not a valid directory.");
		}
		if (!is_dir($config['messagePath'])) {
			throw new Exception("The message path {$config['messagePath']} is not a valid directory.");
		}
		if (empty($config['languages'])) {
			throw new Exception("Languages cannot be empty.");
		}

		$files = FileHelper::findFiles(realpath($config['sourcePath']), $config);

		$messages = array();
		foreach ($files as $file) {
			$messages = array_merge($messages, $this->extractMessages($file, $config['translator']));
		}

		foreach ($config['languages'] as $language) {
			$dir = $config['messagePath'] . DIRECTORY_SEPARATOR . $language;
			if (!is_dir($dir)) {
				@mkdir($dir);
			}
			foreach ($messages as $category => $msgs) {
				$msgs = array_values(array_unique($msgs));
				$this->generateMessageFile($msgs, $dir . DIRECTORY_SEPARATOR . $category . '.php',
					$config['overwrite'],
					$config['removeUnused'],
					$config['sort']);
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
		$messages = array();
		if (!is_array($translator)) {
			$translator = array($translator);
		}
		foreach ($translator as $currentTranslator) {
			$n = preg_match_all(
				'/\b' . $currentTranslator . '\s*\(\s*(\'.*?(?<!\\\\)\'|".*?(?<!\\\\)")\s*,\s*(\'.*?(?<!\\\\)\'|".*?(?<!\\\\)")\s*[,\)]/s',
				$subject, $matches, PREG_SET_ORDER);
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
	 */
	protected function generateMessageFile($messages, $fileName, $overwrite, $removeUnused, $sort)
	{
		echo "Saving messages to $fileName...";
		if (is_file($fileName)) {
			$translated = require($fileName);
			sort($messages);
			ksort($translated);
			if (array_keys($translated) == $messages) {
				echo "nothing new...skipped.\n";
				return;
			}
			$merged = array();
			$untranslated = array();
			foreach ($messages as $message) {
				if (array_key_exists($message, $translated) && strlen($translated[$message]) > 0) {
					$merged[$message] = $translated[$message];
				} else {
					$untranslated[] = $message;
				}
			}
			ksort($merged);
			sort($untranslated);
			$todo = array();
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
			echo "translation merged.\n";
		} else {
			$merged = array();
			foreach ($messages as $message) {
				$merged[$message] = '';
			}
			ksort($merged);
			echo "saved.\n";
		}
		$array = str_replace("\r", '', var_export($merged, true));
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
		file_put_contents($fileName, $content);
	}
}
