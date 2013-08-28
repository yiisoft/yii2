<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use yii\console\Controller;
use yii\helpers\Console;

/**
 * PhpDocController is there to help maintaining PHPDoc annotation in class files
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class PhpDocController extends Controller
{
	/**
	 * Generates @property annotations in class files from getters and setters
	 *
	 * @param string $directory the directory to parse files from
	 * @param boolean $updateFiles whether to update class docs directly
	 */
	public function actionProperty($directory=null, $updateFiles=false)
	{
		if ($directory === null) {
			$directory = dirname(dirname(__DIR__)) . '/framework/yii';
		}

		$nFilesTotal = 0;
	    $files = new \RegexIterator(
	        new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)),
		    '#^.+\.php$#i',
		    \RecursiveRegexIterator::GET_MATCH
	    );
	    foreach ($files as $file) {
	        list($className, $phpdoc) = $this->generateClassPropertyDocs($file[0]);
	        if ($phpdoc !== false) {
		        if ($updateFiles) {
			        $this->updateClassPropertyDocs($file[0], $className, $phpdoc);
		        } else {
		            $this->stdout("\n[ " . $file[0] . " ]\n\n", Console::BOLD);
		            $this->stdout($phpdoc . "\n");
		        }
	        }
	        $nFilesTotal++;
	    }

		$this->stdout("\n\nParsed $nFilesTotal files.\n");
	}

	protected function updateClassPropertyDocs($file, $className, $phpDoc)
	{
		// TODO implement
	}

	protected function generateClassPropertyDocs($fileName)
	{
	    $phpdoc = "";
	    $file = str_replace("\r", "", str_replace("\t", " ", file_get_contents($fileName, true)));
		$ns = $this->match('#\nnamespace (?<name>[\w\\\\]+);\n#', $file);
		$namespace = reset($ns);
		$namespace = $namespace['name'];
	    $classes = $this->match('#\n(?:abstract )?class (?<name>\w+) extends .+\{(?<content>.+)\n\}(\n|$)#', $file);

		if (count($classes) > 1) {
			$this->stderr("[ERR] There should be only one class in a file: $fileName\n", Console::FG_RED);
			return false;
		}
		if (count($classes) < 1) {
			$this->stderr("[ERR] No class in file: $fileName\n", Console::FG_RED);
			return false;
		}

		$className = null;
	    foreach ($classes as &$class) {

		    $className = $namespace . '\\' . $class['name'];

	        $gets = $this->match(
	            '#\* @return (?<type>\w+)(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
	            '[\s\n]{2,}public function (?<kind>get)(?<name>\w+)\((?:,? ?\$\w+ ?= ?[^,]+)*\)#',
	            $class['content']);
	        $sets = $this->match(
	            '#\* @param (?<type>\w+) \$\w+(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
	            '[\s\n]{2,}public function (?<kind>set)(?<name>\w+)\(\$\w+(?:, ?\$\w+ ?= ?[^,]+)*\)#',
	            $class['content']);
	        $acrs = array_merge($gets, $sets);
	        //print_r($acrs); continue;

	        $props = array();
	        foreach ($acrs as &$acr) {
	            $acr['name'] = lcfirst($acr['name']);
	            $acr['comment'] = trim(preg_replace('#(^|\n)\s+\*\s?#', '$1 * ', $acr['comment']));
	            $props[$acr['name']][$acr['kind']] = array(
	                'type' => $acr['type'],
	                'comment' => $this->fixSentence($acr['comment']),
	            );
	        }

//          foreach ($props as $propName => &$prop) // I don't like write-only props...
//				if (!isset($prop['get']))
//				    unset($props[$propName]);

	        if (count($props) > 0) {
	            $phpdoc .= " *\n";
	            foreach ($props as $propName => &$prop) {
	                $phpdoc .= ' * @';
//	                if (isset($prop['get']) && isset($prop['set'])) // Few IDEs support complex syntax
						$phpdoc .= 'property';
//					elseif (isset($prop['get']))
//						$phpdoc .= 'property-read';
//					elseif (isset($prop['set']))
//						$phpdoc .= 'property-write';
	                $phpdoc .= ' ' . $this->getPropParam($prop, 'type') . " $$propName " . $this->getPropParam($prop, 'comment') . "\n";
	            }
	            $phpdoc .= " *\n";
	        }
	    }
	    return array($className, $phpdoc);
	}

	protected function match($pattern, $subject)
	{
	    $sets = array();
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
	    return isset($prop['get']) ? $prop['get'][$param] : $prop['set'][$param];
	}
}