<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\extension;

use Yii;
use yii\gii\CodeFile;

/**
 * This generator will generate the skeleton files needed by an extension.
 *
 * @property string $keywordsArrayJson A json encoded array with the given keywords. This property is
 * read-only.
 * @property boolean $outputPath The directory that contains the module class. This property is read-only.
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $vendorName;
	public $packageName = "yii2-";
	public $namespace;
	public $type = "yii2-extension";
	public $keywords = "yii2,extension";
	public $title;
	public $description;
	public $outputPath = "@app/runtime/tmp-extensions";
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
		return array_merge(
			parent::rules(),
			[
				[['vendorName', 'packageName'], 'filter', 'filter' => 'trim'],
				[
					[
						'vendorName',
						'packageName',
						'namespace',
						'type',
						'license',
						'title',
						'description',
						'authorName',
						'authorEmail',
						'outputPath'
					],
					'required'
				],
				[['keywords'], 'safe'],
				[['authorEmail'], 'email'],
				[
					['vendorName', 'packageName'],
					'match',
					'pattern' => '/^[a-z0-9\-\.]+$/',
					'message' => 'Only lowercase word characters, dashes and dots are allowed.'
				],
				[
					['namespace'],
					'match',
					'pattern' => '/^[a-zA-Z0-9\\\]+\\\$/',
					'message' => 'Only letters, numbers and backslashes are allowed. PSR-4 namespaces must end with a namespace separator.'
				],
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'vendorName'  => 'Vendor Name',
			'packageName' => 'Package Name',
			'license'     => 'License',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function hints()
	{
		return [
			'vendorName'  => 'This refers to the name of the publisher, your GitHub user name is usually a good choice, eg. <code>myself</code>.',
			'packageName' => 'This is the name of the extension on packagist, eg. <code>yii2-foobar</code>.',
			'namespace'   => 'PSR-4, eg. <code>myself\foobar\</code> This will be added to your autoloading by composer. Do not use yii or yii2 in the namespace.',
			'keywords'    => 'Comma separated keywords for this extension.',
			'outputPath'  => 'The temporary location of the generated files.',
			'title'       => 'A more descriptive name of your application for the README file.',
			'description' => 'A sentence or subline describing the main purpose of the extension.',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function stickyAttributes()
	{
		return ['vendorName', 'outputPath', 'authorName', 'authorEmail'];
	}

	/**
	 * @inheritdoc
	 */
	public function successMessage()
	{
		$outputPath = realpath(\Yii::getAlias($this->outputPath));
		$output1 = <<<EOD
<p><em>The extension has been generated successfully.</em></p>
<p>To enable it in your application, you need to create a git repository
and require it via composer.</p>
EOD;
		$code1 = <<<EOD
cd {$outputPath}/{$this->packageName}

git init
git add -A
git commit
git remote add origin https://path.to/your/repo
git push -u origin master
EOD;
		$output2 = <<<EOD
<p>The next step is just for <em>initial development</em>, skip it if you directly publish the extension on packagist.org</p>
<p>Add the newly created repo to your composer.json.</p>
EOD;
		$code2 = <<<EOD
"repositories":[
	{
		"type": "git",
		"url": "https://path.to/your/repo"
	}
]
EOD;
		$output3 = <<<EOD
<p class="well">Note: You may use the url <code>file://{$outputPath}/{$this->packageName}</code> for testing.</p>
<p>Require the package with composer</p>
EOD;
		$code3 = <<<EOD
composer.phar require {$this->vendorName}/{$this->packageName}:dev-master
EOD;
		$output4 = <<<EOD
<p>And use it in your application.</p>
EOD;
		$code4 = <<<EOD
\\{$this->namespace}AutoloadExample::widget();
EOD;
		$output5 = <<<EOD
<p>When you have finished development register your extension at <a href='https://packagist.org/' target='_blank'>packagist.org</a>.</p>
EOD;

		$return = $output1 . '<pre>' . highlight_string($code1, true) . '</pre>';
		$return .= $output2 . '<pre>' . highlight_string($code2, true) . '</pre>';
		$return .= $output3 . '<pre>' . highlight_string($code3, true) . '</pre>';
		$return .= $output4 . '<pre>' . highlight_string($code4, true) . '</pre>';
		$return .= $output5;
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
		$files = [];
		$modulePath = $this->getOutputPath();
		$files[] = new CodeFile(
			$modulePath . '/' . $this->packageName . '/composer.json',
			$this->render("composer.json")
		);
		$files[] = new CodeFile(
			$modulePath . '/' . $this->packageName . '/AutoloadExample.php',
			$this->render("AutoloadExample.php")
		);
		$files[] = new CodeFile(
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
	}

	/**
	 * @return string a json encoded array with the given keywords
	 */
	public function getKeywordsArrayJson()
	{
		return json_encode(explode(',', $this->keywords));
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
