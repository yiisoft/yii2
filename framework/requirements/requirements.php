<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/* @var $this YiiRequirementChecker */

/**
 * These are the Yii core requirements for the [[YiiRequirementChecker]] instance.
 * These requirements are mandatory for any Yii application.
 */
return [
    [
        'name' => 'PHP version',
        'mandatory' => true,
        'condition' => version_compare(PHP_VERSION, '5.4.0', '>='),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
        'memo' => 'PHP 5.4.0 or higher is required.',
    ],
    [
        'name' => 'Reflection extension',
        'mandatory' => true,
        'condition' => class_exists('Reflection', false),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
    ],
    [
        'name' => 'PCRE extension',
        'mandatory' => true,
        'condition' => extension_loaded('pcre'),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
    ],
    [
        'name' => 'SPL extension',
        'mandatory' => true,
        'condition' => extension_loaded('SPL'),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
    ],
    [
        'name' => 'Ctype extension',
        'mandatory' => true,
        'condition' => extension_loaded('ctype'),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
    ],
    [
        'name' => 'MBString extension',
        'mandatory' => true,
        'condition' => extension_loaded('mbstring'),
        'by' => '<a href="http://www.php.net/manual/en/book.mbstring.php">Multibyte string</a> processing',
        'memo' => 'Required for multibyte encoding string processing.',
    ],
    [
        'name' => 'OpenSSL extension',
        'mandatory' => false,
        'condition' => extension_loaded('openssl'),
        'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-base-security.html">Security Component</a>',
        'memo' => 'Required by encrypt and decrypt methods.',
    ],
    [
        'name' => 'Intl extension',
        'mandatory' => false,
        'condition' => $this->checkPhpExtensionVersion('intl', '1.0.2', '>='),
        'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
        'memo' => 'PHP Intl extension 1.0.2 or higher is required when you want to use advanced parameters formatting
        in <code>Yii::t()</code>, non-latin languages with <code>Inflector::slug()</code>,
        <abbr title="Internationalized domain names">IDN</abbr>-feature of
        <code>EmailValidator</code> or <code>UrlValidator</code> or the <code>yii\i18n\Formatter</code> class.',
    ],
    [
        'name' => 'ICU version',
        'mandatory' => false,
        'condition' => defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '49', '>='),
        'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
        'memo' => 'ICU 49.0 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href=\"http://www.yiiframework.com/doc-2.0/yii-i18n-formatter.html#asRelativeTime%28%29-detail\">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU version is ' .
        (defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : '(ICU is missing)') . '.',
    ],
    [
        'name' => 'ICU Data version',
        'mandatory' => false,
        'condition' => defined('INTL_ICU_DATA_VERSION') && version_compare(INTL_ICU_DATA_VERSION, '49.1', '>='),
        'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
        'memo' => 'ICU Data 49.1 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href=\"http://www.yiiframework.com/doc-2.0/yii-i18n-formatter.html#asRelativeTime%28%29-detail\">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU Data version is ' .
        (defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : '(ICU Data is missing)') . '.',
    ],
    [
        'name' => 'Fileinfo extension',
        'mandatory' => false,
        'condition' => extension_loaded('fileinfo'),
        'by' => '<a href="http://www.php.net/manual/en/book.fileinfo.php">File Information</a>',
        'memo' => 'Required for files upload to detect correct file mime-types.',
    ],
    [
        'name' => 'DOM extension',
        'mandatory' => false,
        'condition' => extension_loaded('dom'),
        'by' => '<a href="http://php.net/manual/en/book.dom.php">Document Object Model</a>',
        'memo' => 'Required for REST API to send XML responses via <code>yii\web\XmlResponseFormatter</code>.',
    ],
    [
        'name' => 'IPv6 support',
        'mandatory' => false,
        'condition' => defined('AF_INET6'),
        'by' => 'IPv6 expansion in <a href="http://www.yiiframework.com/doc-2.0/yii-validators-ipvalidator.html">IpValidator</a>',
        'memo' => 'When <a href="http://www.yiiframework.com/doc-2.0/yii-validators-ipvalidator.html#$expandIPv6-detail">IpValidator::expandIPv6</a>
        property is set to <code>true</code>, PHP must support IPv6 protocol stack. Currently PHP constant <code>AF_INET6</code> is not defined
        and IPv6 is probably unsupported.',
    ],
];
