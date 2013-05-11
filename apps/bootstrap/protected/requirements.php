<?php
/**
 * Application requirement checker script.
 *
 * In order to run this script use the following console command:
 * php requirements.php
 *
 * In order to run this script from the web, you should copy it to the web root.
 * If you are using Linux you can create a hard link instead, using the following command:
 * ln requirements.php ../requirements.php
 */

$appRootPath = dirname(__FILE__);
if (basename($appRootPath) == 'protected') {
	$appRootPath = dirname($appRootPath);
}
// you may need to adjust this path:
require_once(realpath($appRootPath.'/../../yii/requirements/YiiRequirementChecker.php'));
$requirementsChecker = new YiiRequirementChecker();

/**
 * Adjust requirements according to your application specifics.
 */
$requirements = array(
	// Database :
	array(
		'name' => 'PDO extension',
		'mandatory' => true,
		'condition' => extension_loaded('pdo'),
		'by' => 'All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>',
	),
	array(
		'name' => 'PDO SQLite extension',
		'mandatory' => false,
		'condition' => extension_loaded('pdo_sqlite'),
		'by' => 'All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>',
		'memo' => 'Required for SQLite database.',
	),
	array(
		'name' => 'PDO MySQL extension',
		'mandatory' => false,
		'condition' => extension_loaded('pdo_mysql'),
		'by' => 'All <a href="http://www.yiiframework.com/doc/api/#system.db">DB-related classes</a>',
		'memo' => 'Required for MySQL database.',
	),
	// Cache :
	array(
		'name' => 'Memcache extension',
		'mandatory' => false,
		'condition' => extension_loaded('memcache') || extension_loaded('memcached'),
		'by' => '<a href="http://www.yiiframework.com/doc/api/CMemCache">CMemCache</a>',
		'memo' => extension_loaded('memcached') ? 'To use memcached set <a href="http://www.yiiframework.com/doc/api/CMemCache#useMemcached-detail">CMemCache::useMemcached</a> to <code>true</code>.' : ''
	),
	array(
		'name' => 'APC extension',
		'mandatory' => false,
		'condition' => extension_loaded('apc') || extension_loaded('apc'),
		'by' => '<a href="http://www.yiiframework.com/doc/api/CApcCache">CApcCache</a>',
	),
	// Additional PHP extensions :
	array(
		'name' => 'Mcrypt extension',
		'mandatory' => false,
		'condition' => extension_loaded('mcrypt'),
		'by' => '<a href="http://www.yiiframework.com/doc/api/CSecurityManager">CSecurityManager</a>',
		'memo' => 'Required by encrypt and decrypt methods.'
	),
	// PHP ini :
	'phpSafeMode' => array(
		'name' => 'PHP safe mode',
		'mandatory' => false,
		'condition' => $requirementsChecker->checkPhpIniOff("safe_mode"),
		'by' => 'File uploading and console command execution',
		'memo' => '"safe_mode" should be disabled at php.ini',
	),
	'phpExposePhp' => array(
		'name' => 'Expose PHP',
		'mandatory' => false,
		'condition' => $requirementsChecker->checkPhpIniOff("expose_php"),
		'by' => 'Security reasons',
		'memo' => '"expose_php" should be disabled at php.ini',
	),
	'phpAllowUrlInclude' => array(
		'name' => 'PHP allow url include',
		'mandatory' => false,
		'condition' => $requirementsChecker->checkPhpIniOff("allow_url_include"),
		'by' => 'Security reasons',
		'memo' => '"allow_url_include" should be disabled at php.ini',
	),
	'phpSmtp' => array(
		'name' => 'PHP mail SMTP',
		'mandatory' => false,
		'condition' => strlen(ini_get('SMTP'))>0,
		'by' => 'Email sending',
		'memo' => 'PHP mail SMTP server required',
	),
);
$requirementsChecker->checkYii()->check($requirements)->render();