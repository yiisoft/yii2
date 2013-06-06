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
class PDO extends \PDO
{

	const OPT_SEARCH_PATH = 'search_path';
	const OPT_DEFAULT_SCHEMA = 'default_schema';
	const DEFAULT_SCHEMA = 'public';

	private $_currentDatabase;

	/**
	 * Returns value of the last inserted ID.
	 * @param string|null $sequence the sequence name. Defaults to null.
	 * @return integer last inserted ID value.
	 */
	public function lastInsertId($sequence = null) {
		if ($sequence !== null) {
			$sequence = $this->quote($sequence);
			return $this->query("SELECT currval({$sequence})")->fetchColumn();
		} else {
			return null;
		}
	}

	/**
	 * Here we override the default PDO constructor in order to 
	 * find and set the default schema search path.
	 */
	public function __construct($dsn, $username, $passwd, $options) {
		$searchPath = null;
		if (is_array($options)) {
			if (isset($options[self::OPT_SEARCH_PATH])) {
				$matches = null;
				if (preg_match("/(\s?)+(\w)+((\s+)?,(\s+)?\w+)*/", $options[self::OPT_SEARCH_PATH], $matches) === 1) {
					$searchPath = $matches[0];
				}
			}
			if (isset($options[self::OPT_DEFAULT_SCHEMA])) {
				$schema = trim($options[self::OPT_DEFAULT_SCHEMA]);
				if (!empty($schema)) {
					Schema::$DEFAULT_SCHEMA = $schema;
				}
			}
			if (is_null(Schema::$DEFAULT_SCHEMA) || empty(Schema::$DEFAULT_SCHEMA)) {
				Schema::$DEFAULT_SCHEMA = self::DEFAULT_SCHEMA;
			}
		}
		parent::__construct($dsn, $username, $passwd, $options);
		if (!is_null($searchPath)) {
			$this->setSchemaSearchPath($searchPath);
		}
	}

	/**
	 * Returns the name of the current (connected) database 
	 * @return string
	 */
	public function getCurrentDatabase() {
		if (is_null($this->_currentDatabase)) {
			return $this->query('select current_database()')->fetchColumn();
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
