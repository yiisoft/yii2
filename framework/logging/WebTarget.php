<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebLogRoute shows the log content in Web page.
 *
 * The log content can appear either at the end of the current Web page
 * or in FireBug console window (if {@link showInFireBug} is set true).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CWebLogRoute extends CLogRoute
{
	/**
	 * @var boolean whether the log should be displayed in FireBug instead of browser window. Defaults to false.
	 */
	public $showInFireBug = false;

	/**
	 * @var boolean whether the log should be ignored in FireBug for ajax calls. Defaults to true.
	 * This option should be used carefully, because an ajax call returns all output as a result data.
	 * For example if the ajax call expects a json type result any output from the logger will cause ajax call to fail.
	 */
	public $ignoreAjaxInFireBug = true;

	/**
	 * Displays the log messages.
	 * @param array $logs list of log messages
	 */
	public function processLogs($logs)
	{
		$this->render('log', $logs);
	}

	/**
	 * Renders the view.
	 * @param string $view the view name (file name without extension). The file is assumed to be located under framework/data/views.
	 * @param array $data data to be passed to the view
	 */
	protected function render($view, $data)
	{
		$app = \Yii::$app;
		$isAjax = $app->getRequest()->getIsAjaxRequest();

		if ($this->showInFireBug)
		{
			if ($isAjax && $this->ignoreAjaxInFireBug)
				return;
			$view .= '-firebug';
		} elseif (!($app instanceof CWebApplication) || $isAjax)
			return;

		$viewFile = YII_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
		include($app->findLocalizedFile($viewFile, 'en'));
	}
}