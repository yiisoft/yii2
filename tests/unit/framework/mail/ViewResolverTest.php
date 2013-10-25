<?php

namespace yiiunit\framework\mail;

use yii\mail\ViewResolver;
use Yii;
use yiiunit\TestCase;

/**
 * @group email
 */
class ViewResolverTest extends TestCase
{
	/**
	 * @var string test email view path.
	 */
	protected $testViewPath = '@yiiunit/emails';

	/**
	 * Data provider for [[testFindViewFile()]]
	 * @return array test data.
	 */
	public function dataProviderFindViewFile()
	{
		$alias = '@yiiunit';
		$aliasPath = Yii::getAlias($alias);
		$viewPath = Yii::getAlias($this->testViewPath);
		return [
			[
				$alias . '/test',
				$aliasPath . '/test.php',
			],
			[
				$alias . '/test.tpl',
				$aliasPath . '/test.tpl',
			],
			[
				'contact/html',
				$viewPath . '/contact/html.php',
			],
			[
				'contact/html.tpl',
				$viewPath . '/contact/html.tpl',
			],
		];
	}

	/**
	 * @dataProvider dataProviderFindViewFile
	 *
	 * @param string $view
	 * @param string $expectedFileName
	 */
	public function testFindViewFile($view, $expectedFileName)
	{
		$viewResolver = new ViewResolver();
		$viewResolver->viewPath = $this->testViewPath;
		$fileName = $viewResolver->findViewFile($view);
		$this->assertEquals($expectedFileName, $fileName);
	}
}