<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 *
 * @var YiiRequirementChecker $this
 *
 * These are the Yii core requirements for the [[YiiRequirementChecker]] instance.
 * These requirements are mandatory for any Yii application.
 */
return array(
    array(
        'name' => 'PHP version',
        'mandatory' => true,
        'condition' => version_compare(PHP_VERSION, '7.3.0', '>='),
        'by' => '<a href="https://www.yiiframework.com">Yii Framework</a>',
        'memo' => 'PHP 7.3.0 or higher is required.',
    ),
    array(
        'name' => 'Reflection extension',
        'mandatory' => true,
        'condition' => class_exists('Reflection', false),
        'by' => '<a href="https://www.yiiframework.com">Yii Framework</a>',
    ),
    array(
        'name' => 'PCRE extension',
        'mandatory' => true,
        'condition' => extension_loaded('pcre'),
        'by' => '<a href="https://www.yiiframework.com">Yii Framework</a>',
    ),
    array(
        'name' => 'SPL extension',
        'mandatory' => true,
        'condition' => extension_loaded('SPL'),
        'by' => '<a href="https://www.yiiframework.com">Yii Framework</a>',
    ),
    array(
        'name' => 'Ctype extension',
        'mandatory' => true,
        'condition' => extension_loaded('ctype'),
        'by' => '<a href="https://www.yiiframework.com">Yii Framework</a>'
    ),
    array(
        'name' => 'MBString extension',
        'mandatory' => true,
        'condition' => extension_loaded('mbstring'),
        'by' => '<a href="https://www.php.net/manual/en/book.mbstring.php">Multibyte string</a> processing',
        'memo' => 'Required for multibyte encoding string processing.'
    ),
    array(
        'name' => 'OpenSSL extension',
        'mandatory' => false,
        'condition' => extension_loaded('openssl'),
        'by' => '<a href="https://www.yiiframework.com/doc-2.0/yii-base-security.html">Security Component</a>',
        'memo' => 'Required by encrypt and decrypt methods.'
    ),
    array(
        'name' => 'Intl extension',
        'mandatory' => false,
        'condition' => $this->checkPhpExtensionVersion('intl', '1.0.2', '>='),
        'by' => '<a href="https://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
        'memo' => 'PHP Intl extension 1.0.2 or higher is required when you want to use advanced parameters formatting
        in <code>Yii::t()</code>, non-latin languages with <code>Inflector::slug()</code>,
        <abbr title="Internationalized domain names">IDN</abbr>-feature of
        <code>EmailValidator</code> or <code>UrlValidator</code> or the <code>yii\i18n\Formatter</code> class.'
    ),
    array(
        'name' => 'ICU version',
        'mandatory' => false,
        'condition' => defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '49', '>='),
        'by' => '<a href="https://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
        'memo' => 'ICU 49.0 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href=\"https://www.yiiframework.com/doc-2.0/yii-i18n-formatter.html#asRelativeTime%28%29-detail\">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU version is ' .
        (defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : '(ICU is missing)') . '.'
    ),
    array(
        'name' => 'ICU Data version',
        'mandatory' => false,
        'condition' => defined('INTL_ICU_DATA_VERSION') && version_compare(INTL_ICU_DATA_VERSION, '49.1', '>='),
        'by' => '<a href="https://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
        'memo' => 'ICU Data 49.1 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href=\"https://www.yiiframework.com/doc-2.0/yii-i18n-formatter.html#asRelativeTime%28%29-detail\">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU Data version is ' .
        (defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : '(ICU Data is missing)') . '.'
    ),
    array(
        'name' => 'Fileinfo extension',
        'mandatory' => false,
        'condition' => extension_loaded('fileinfo'),
        'by' => '<a href="https://www.php.net/manual/en/book.fileinfo.php">File Information</a>',
        'memo' => 'Required for files upload to detect correct file mime-types.'
    ),
    array(
        'name' => 'DOM extension',
        'mandatory' => false,
        'condition' => extension_loaded('dom'),
        'by' => '<a href="https://www.php.net/manual/en/book.dom.php">Document Object Model</a>',
        'memo' => 'Required for REST API to send XML responses via <code>yii\web\XmlResponseFormatter</code>.'
    ),
    array(
        'name' => 'IPv6 support',
        'mandatory' => false,
        'condition' => strlen(@inet_pton('2001:db8::1')) === 16,
        'by' => 'IPv6 expansion in <a href="https://www.yiiframework.com/doc-2.0/yii-validators-ipvalidator.html">IpValidator</a>',
        'memo' => 'When <a href="https://www.yiiframework.com/doc-2.0/yii-validators-ipvalidator.html#$expandIPv6-detail">IpValidator::expandIPv6</a>
        property is set to <code>true</code>, PHP must support IPv6 protocol stack. Currently PHP constant <code>AF_INET6</code> is not defined
        and IPv6 is probably unsupported.'
    ),
    array(
        'name' => 'pcntl',
        'mandatory' => false,
        'condition' => extension_loaded('pcntl'),
        'by' => '<a href="https://www.php.net/manual/de/book.pcntl.php">Process Control</a>',
        'memo' => 'Recommended for yii2-queue CLI operations'
    )
);
