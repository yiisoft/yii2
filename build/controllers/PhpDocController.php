<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * PhpDocController is there to help maintaining PHPDoc annotation in class files
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class PhpDocController extends Controller
{
	public $defaultAction = 'property';

	/**
	 * @var bool whether to update class docs directly. Setting this to false will just output docs
	 * for copy and paste.
	 */
	public $updateFiles = true;

	/**
	 * Generates @property annotations in class files from getters and setters
	 *
	 * Property description will be taken from getter or setter or from an @property annotation
	 * in the getters docblock if there is one defined.
	 *
	 * See https://github.com/yiisoft/yii2/wiki/Core-framework-code-style#documentation for details.
	 *
	 * @param null $root the directory to parse files from. Defaults to YII_PATH.
	 */
	public function actionProperty($root=null)
	{
		if ($root === null) {
			$root = YII_PATH;
		}
		$root = FileHelper::normalizePath($root);
		$options = [
			'filter' => function ($path) {
				if (is_file($path)) {
					$file = basename($path);
					if ($file[0] < 'A' || $file[0] > 'Z') {
						return false;
					}
				}
				return null;
			},
			'only' => ['.php'],
			'except' => [
				'YiiBase.php',
				'Yii.php',
				'/debug/views/',
				'/requirements/',
				'/gii/views/',
				'/gii/generators/',
			],
		];
		$files = FileHelper::findFiles($root, $options);
		$nFilesTotal = 0;
		$nFilesUpdated = 0;
		foreach ($files as $file) {
			$result = $this->generateClassPropertyDocs($file);
			if ($result !== false) {
				list($className, $phpdoc) = $result;
				if ($this->updateFiles) {
					if ($this->updateClassPropertyDocs($file, $className, $phpdoc)) {
						$nFilesUpdated++;
					}
				} elseif (!empty($phpdoc)) {
					$this->stdout("\n[ " . $file . " ]\n\n", Console::BOLD);
					$this->stdout($phpdoc);
				}
			}
			$nFilesTotal++;
		}

		$this->stdout("\nParsed $nFilesTotal files.\n");
		$this->stdout("Updated $nFilesUpdated files.\n");
	}

	public function globalOptions()
	{
		return array_merge(parent::globalOptions(), ['updateFiles']);
	}

	protected function updateClassPropertyDocs($file, $className, $propertyDoc)
	{
		$ref = new \ReflectionClass($className);
		if ($ref->getFileName() != $file) {
			$this->stderr("[ERR] Unable to create ReflectionClass for class: $className loaded class is not from file: $file\n", Console::FG_RED);
		}

		if (!$ref->isSubclassOf('yii\base\Object') && $className != 'yii\base\Object') {
			$this->stderr("[INFO] Skipping class $className as it is not a subclass of yii\\base\\Object.\n", Console::FG_BLUE, Console::BOLD);
			return false;
		}

		$oldDoc = $ref->getDocComment();
		$newDoc = $this->cleanDocComment($this->updateDocComment($oldDoc, $propertyDoc));

		$seenSince = false;
		$seenAuthor = false;

		// TODO move these checks to different action
		$lines = explode("\n", $newDoc);
		if (trim($lines[1]) == '*' || substr(trim($lines[1]), 0, 3) == '* @') {
			$this->stderr("[WARN] Class $className has no short description.\n", Console::FG_YELLOW, Console::BOLD);
		}
		foreach($lines as $line) {
			if (substr(trim($line), 0, 9) == '* @since ') {
				$seenSince = true;
			} elseif (substr(trim($line), 0, 10) == '* @author ') {
				$seenAuthor = true;
			}
		}

		if (!$seenSince) {
			$this->stderr("[ERR] No @since found in class doc in file: $file\n", Console::FG_RED);
		}
		if (!$seenAuthor) {
			$this->stderr("[ERR] No @author found in class doc in file: $file\n", Console::FG_RED);
		}

		if (trim($oldDoc) != trim($newDoc)) {

			$fileContent = explode("\n", file_get_contents($file));
			$start = $ref->getStartLine() - 2;
			$docStart = $start - count(explode("\n", $oldDoc)) + 1;

			$newFileContent = [];
			$n = count($fileContent);
			for($i = 0; $i < $n; $i++) {
				if ($i > $start || $i < $docStart) {
					$newFileContent[] = $fileContent[$i];
				} else {
					$newFileContent[] = trim($newDoc);
					$i = $start;
				}
			}

			file_put_contents($file, implode("\n", $newFileContent));

			return true;
		}
		return false;
	}

	/**
	 * remove multi empty lines and trim trailing whitespace
	 *
	 * @param $doc
	 * @return string
	 */
	protected function cleanDocComment($doc)
	{
		$lines = explode("\n", $doc);
		$n = count($lines);
		for($i = 0; $i < $n; $i++) {
			$lines[$i] = rtrim($lines[$i]);
			if (trim($lines[$i]) == '*' && trim($lines[$i + 1]) == '*') {
				unset($lines[$i]);
			}
		}
		return implode("\n", $lines);
	}

	/**
	 * replace property annotations in doc comment
	 * @param $doc
	 * @param $properties
	 * @return string
	 */
	protected function updateDocComment($doc, $properties)
	{
		$lines = explode("\n", $doc);
		$propertyPart = false;
		$propertyPosition = false;
		foreach($lines as $i => $line) {
			if (substr(trim($line), 0, 12) == '* @property ') {
				$propertyPart = true;
			} elseif ($propertyPart && trim($line) == '*') {
				$propertyPosition = $i;
				$propertyPart = false;
			}
			if (substr(trim($line), 0, 10) == '* @author ' && $propertyPosition === false) {
				$propertyPosition = $i - 1;
				$propertyPart = false;
			}
			if ($propertyPart) {
				unset($lines[$i]);
			}
		}
		$finalDoc = '';
		foreach($lines as $i => $line) {
			$finalDoc .= $line . "\n";
			if ($i == $propertyPosition) {
				$finalDoc .= $properties;
			}
		}
		return $finalDoc;
	}

	protected function generateClassPropertyDocs($fileName)
	{
		$phpdoc = "";
		$file = str_replace("\r", "", str_replace("\t", " ", file_get_contents($fileName, true)));
		$ns = $this->match('#\nnamespace (?<name>[\w\\\\]+);\n#', $file);
		$namespace = reset($ns);
		$namespace = $namespace['name'];
		$classes = $this->match('#\n(?:abstract )?class (?<name>\w+)( |\n)(extends )?.+\{(?<content>.*)\n\}(\n|$)#', $file);

		if (count($classes) > 1) {
			$this->stderr("[ERR] There should be only one class in a file: $fileName\n", Console::FG_RED);
			return false;
		}
		if (count($classes) < 1) {
			$interfaces = $this->match('#\ninterface (?<name>\w+)\n\{(?<content>.+)\n\}(\n|$)#', $file);
			if (count($interfaces) == 1) {
				return false;
			} elseif (count($interfaces) > 1) {
				$this->stderr("[ERR] There should be only one interface in a file: $fileName\n", Console::FG_RED);
			} else {
				$this->stderr("[ERR] No class in file: $fileName\n", Console::FG_RED);
			}
			return false;
		}

		$className = null;
		foreach ($classes as &$class) {

			$className = $namespace . '\\' . $class['name'];

			$gets = $this->match(
				'#\* @return (?<type>[\w\\|\\\\\\[\\]]+)(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
				'[\s\n]{2,}public function (?<kind>get)(?<name>\w+)\((?:,? ?\$\w+ ?= ?[^,]+)*\)#',
				$class['content']);
			$sets = $this->match(
				'#\* @param (?<type>[\w\\|\\\\\\[\\]]+) \$\w+(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
				'[\s\n]{2,}public function (?<kind>set)(?<name>\w+)\(\$\w+(?:, ?\$\w+ ?= ?[^,]+)*\)#',
				$class['content']);
			// check for @property annotations in getter and setter
			$properties = $this->match(
				'#\* @(?<kind>property) (?<type>[\w\\|\\\\\\[\\]]+)(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
				'[\s\n]{2,}public function [g|s]et(?<name>\w+)\(((?:,? ?\$\w+ ?= ?[^,]+)*|\$\w+(?:, ?\$\w+ ?= ?[^,]+)*)\)#',
				$class['content']);
			$acrs = array_merge($properties, $gets, $sets);
			//print_r($acrs); continue;

			$props = [];
			foreach ($acrs as &$acr) {
				$acr['name'] = lcfirst($acr['name']);
				$acr['comment'] = trim(preg_replace('#(^|\n)\s+\*\s?#', '$1 * ', $acr['comment']));
				$props[$acr['name']][$acr['kind']] = [
					'type' => $acr['type'],
					'comment' => $this->fixSentence($acr['comment']),
				];
			}

			ksort($props);

			if (count($props) > 0) {
				$phpdoc .= " *\n";
				foreach ($props as $propName => &$prop) {
					$docline = ' * @';
					$docline .= 'property'; // Do not use property-read and property-write as few IDEs support complex syntax.
					$note = '';
					if (isset($prop['get']) && isset($prop['set'])) {
						if ($prop['get']['type'] != $prop['set']['type']) {
							$note = ' Note that the type of this property differs in getter and setter.'
								  . ' See [[get'.ucfirst($propName).'()]] and [[set'.ucfirst($propName).'()]] for details.';
						}
					} elseif (isset($prop['get'])) {
						$note = ' This property is read-only.';
//						$docline .= '-read';
					} elseif (isset($prop['set'])) {
						$note = ' This property is write-only.';
//						$docline .= '-write';
					} else {
						continue;
					}
					$docline .= ' ' . $this->getPropParam($prop, 'type') . " $$propName ";
					$comment = explode("\n", $this->getPropParam($prop, 'comment') . $note);
					foreach ($comment as &$cline) {
						$cline = ltrim($cline, '* ');
					}
					$docline = wordwrap($docline . implode(' ', $comment), 110, "\n * ") . "\n";

					$phpdoc .= $docline;
				}
				$phpdoc .= " *\n";
			}
		}
		return [$className, $phpdoc];
	}

	protected function match($pattern, $subject)
	{
		$sets = [];
		preg_match_all($pattern . 'suU', $subject, $sets, PREG_SET_ORDER);
		foreach ($sets as &$set)
			foreach ($set as $i => $match)
				if (is_numeric($i) /*&& $i != 0*/)
					unset($set[$i]);
		return $sets;
	}

	protected function fixSentence($str)
	{
		// TODO fix word wrap
		if ($str == '')
			return '';
		return strtoupper(substr($str, 0, 1)) . substr($str, 1) . ($str[strlen($str) - 1] != '.' ? '.' : '');
	}

	protected function getPropParam($prop, $param)
	{
		return isset($prop['property']) ? $prop['property'][$param] : (isset($prop['get']) ? $prop['get'][$param] : $prop['set'][$param]);
	}
}