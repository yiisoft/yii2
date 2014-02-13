<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\extension;

use yii\gii\CodeFile;
use yii\helpers\Html;
use Yii;
use yii\helpers\StringHelper;

/**
 * This generator will generate the skeleton files needed by an extension.
 *
 * @property tbd
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $vendorName;
	public $packageName;
	public $namespace;
	public $type;
	public $keywords = "[yii2], [need array handling here]";
	public $title;
	public $description;
	public $outputPath = "@app/tmp";
	public $license;
	public $authorName;
	public $authorEmail;

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Extension Generator';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return 'This generator helps you to generate the files needed by a Yii extension.';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array_merge(parent::rules(), [
			[['vendorName', 'packageName'], 'filter', 'filter' => 'trim'],
			[['vendorName', 'packageName', 'namespace', 'type', 'license', 'title','description', 'authorName','authorEmail'], 'required'],
			[['authorEmail'], 'email'],
			[['packageName'], 'match', 'pattern' => '/^[a-z0-9-\.]+$/', 'message' => 'Only lowercase word characters, dashes and dots are allowed.'],
			[['vendorName'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'vendorName' => 'Vendor Name',
			'packageName' => 'Package Name',
			'license' => 'License',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return [
			'vendorName' => 'This refers to the name of the publisher, often i.e. your GitHub user name.',
			'packageName' => 'This is the name of the extension.',
			'namespace' => 'This will be added to your autoloader by composer.',
			'outputPath' => 'The temporary location of the generated files.',
			'title' => 'A more descriptive name of your application for the README file.',
			'description' => 'A sentence or subline describing the main purpose of the extension.',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return ['vendorName','outputPath','authorName','authorEmail'];
	}

	/**
	 * @inheritdoc
	 */
	public function successMessage()
	{
		#if (Yii::$app->hasModule($this->moduleID)) {
		#	$link = Html::a('try it now', Yii::$app->getUrlManager()->createUrl($this->moduleID), ['target' => '_blank']);
		#	return "The module has been generated successfully. You may $link.";
		#}

		$output1 = <<<EOD
<p>The extension has been generated successfully.</p>
<p>To enable it in your application, you need to create a git repository
and require via composer.</p>
EOD;
		$code1 = <<<EOD
cd tmp/{$this->packageName}

git init
git add -A
git commit
EOD;
		$output2 = <<<EOD
<p>The next step is just for <em>local testing</em>, skip it if you directly publish the extension on e.g. packagist.org</p>
<p>Add the newly created repo to your composer.json.</p>
EOD;
		$code2 = <<<EOD
"repositories":[
	{
		"type": "git",
		"url": "file://./tmp/{$this->packageName}"
	}
]
EOD;
		$output3 = <<<EOD
<p>Note: Make sure to remove the above lines after testing.</p>
<p>Require the package with composer</p>
EOD;
		$code3 = <<<EOD
composer.phar require {$this->vendorName}/yii2-{$this->packageName}:*
EOD;
		$output4 = <<<EOD
<p>And use it in your application.</p>
EOD;
		$code4 = <<<EOD
\$x = new \\{$this->vendorName}\\{$this->packageName}\AutoloadExample::widget();
echo \$x->run();
EOD;
		$return = $output1 . '<pre>' . highlight_string($code1, true) . '</pre>';
		$return .= $output2 . '<pre>' . highlight_string($code2, true) . '</pre>';
		$return .= $output3 . '<pre>' . highlight_string($code3, true) . '</pre>';
		$return .= $output4 . '<pre>' . highlight_string($code4, true) . '</pre>';
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function requiredTemplates()
	{
		return ['composer.json', 'AutoloadExample.php', 'README.md'];
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		$files      = [];
		$modulePath = $this->getOutputPath();
		$files[]    = new CodeFile(
			$modulePath . '/' . $this->packageName . '/composer.json',
			$this->render("composer.json")
		);
		$files[]    = new CodeFile(
			$modulePath . '/' . $this->packageName . '/AutoloadExample.php',
			$this->render("AutoloadExample.php")
		);
		$files[]    = new CodeFile(
			$modulePath . '/' . $this->packageName . '/README.md',
			$this->render("README.md")
		);
		return $files;
	}

	/**
	 * @return boolean the directory that contains the module class
	 */
	public function getOutputPath()
	{
		return Yii::getAlias($this->outputPath);
		#return Yii::getAlias('@' . str_replace('\\', '/', substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\'))));
	}

	/**
	 * @return array options for type drop-down
	 */
	public function optsType()
	{
		$licenses = [
			'yii2-extension',
			'library',
		];
		return array_combine($licenses, $licenses);
	}

	/**
	 * @return array options for license drop-down
	 */
	public function optsLicense()
	{
		$licenses = [
			'Apache-2.0',
			'BSD-2-Clause',
			'BSD-3-Clause',
			'BSD-4-Clause',
			'GPL-2.0',
			'GPL-2.0+',
			'GPL-3.0',
			'GPL-3.0+',
			'LGPL-2.1',
			'LGPL-2.1+',
			'LGPL-3.0',
			'LGPL-3.0+',
			'MIT'
		];
		return array_combine($licenses, $licenses);
	}
}
