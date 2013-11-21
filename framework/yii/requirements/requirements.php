<?php
/**
 * These are the Yii core requirements for the [[YiiRequirementChecker]] instance.
 * These requirements are mandatory for any Yii application.
 *
 * @var YiiRequirementChecker $this
 */
return array(
	array(
		'name' => 'PHP version',
		'mandatory' => true,
		'condition' => version_compare(PHP_VERSION, '5.4.0', '>='),
		'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
		'memo' => 'PHP 5.4.0 or higher is required.',
	),
	array(
		'name' => 'Reflection extension',
		'mandatory' => true,
		'condition' => class_exists('Reflection', false),
		'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
	),
	array(
		'name' => 'PCRE extension',
		'mandatory' => true,
		'condition' => extension_loaded('pcre'),
		'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
	),
	array(
		'name' => 'SPL extension',
		'mandatory' => true,
		'condition' => extension_loaded('SPL'),
		'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
	),
	array(
		'name' => 'MBString extension',
		'mandatory' => true,
		'condition' => extension_loaded('mbstring'),
		'by' => '<a href="http://www.php.net/manual/en/book.mbstring.php">Multibyte string</a> processing',
		'memo' => 'Required for multibyte encoding string processing.'
	),
	array(
		'name' => 'Intl extension',
		'mandatory' => false,
		'condition' => $this->checkPhpExtensionVersion('intl', '1.0.2', '>='),
		'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
		'memo' => 'PHP Intl extension 1.0.2 or higher is required when you want to use advanced parameters formatting
		in <code>Yii::t()</code>, <abbr title="Internationalized domain names">IDN</abbr>-feature of
		<code>EmailValidator</code> or <code>UrlValidator</code> or the <code>yii\i18n\Formatter</code> class.'
	),
);
