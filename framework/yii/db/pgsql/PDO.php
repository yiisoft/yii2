<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

/**
 * This is an extension of the default PDO class for PostgreSQL drivers.
 * It provides additional low level functionality for setting database 
 * configuration parameters.
 *
 * @author Gevik babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class PDO extends \PDO {

	/**
	 * Here we override the default PDO constructor in order to 
	 * find and set the default schema search path.
	 */
	public function __construct($dsn, $username, $passwd, $options) {
		$searchPath = null;
		if (is_array($options) && isset($options['search_path'])) {
			$matches = null;
			if (preg_match("/(\s?)+(\w)+((\s+)?,(\s+)?\w+)*/", $options['search_path'], $matches) === 1) {
				$searchPath = $matches[0];
			}
		}
		parent::__construct($dsn, $username, $passwd, $options);
		if (!is_null($searchPath)) {
			$this->setSchemaSearchPath($searchPath);
		}
	}

	/**
	 * Sets the schema search path of the current users session.
	 * The syntax of the path is a comma separated string with 
	 * your custom search path at the beginning and the "public"
	 * schema at the end. 
	 * 
	 * This method automatically adds the "public" schema at the 
	 * end of the search path if it is not provied.
	 * @param string custom schema search path. defaults to public
	 */
	public function setSchemaSearchPath($searchPath = 'public') {
		$schemas = explode(',', str_replace(' ', '', $searchPath));
		if (end($schemas) !== 'public') {
			$schemas[] = 'public';
		}
		foreach ($schemas as $k => $item) {
			$schemas[$k] = '"' . str_replace(array('"', "'", ';'), '', $item) . '"';
		}
		$path = implode(', ', $schemas);
		$this->exec('SET search_path TO ' . $path);
	}

}
