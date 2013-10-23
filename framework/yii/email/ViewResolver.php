<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

use yii\base\Component;
use Yii;

/**
 * ViewResolver handles the search for the view files, which are rendered
 * by email messages.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ViewResolver extends Component
{
	/**
	 * @var string directory containing view files for this email messages.
	 */
	public $viewPath = '@app/emails';

	/**
	 * Finds the view file based on the given view name.
	 * The view to be rendered can be specified in one of the following formats:
	 * - path alias (e.g. "@app/emails/contact/body");
	 * - relative path (e.g. "contact"): the actual view file will be resolved by [[resolveView]].
	 * @param string $view the view name or the path alias of the view file.
	 * @return string the view file path. Note that the file may not exist.
	 */
	public function findViewFile($view)
	{
		if (strncmp($view, '@', 1) === 0) {
			// e.g. "@app/views/main"
			$file = Yii::getAlias($view);
		} else {
			$file = $this->resolveView($view);
		}
		return pathinfo($file, PATHINFO_EXTENSION) === '' ? $file . '.php' : $file;
	}

	/**
	 * Composes file name for the view name, appending view name to [[viewPath]].
	 * Child classes may override this method to provide more sophisticated
	 * search of the view files or even composition of the view files "on the fly".
	 * @param string $view the view name.
	 * @return string the view file path.
	 */
	protected function resolveView($view)
	{
		return Yii::getAlias($this->viewPath) . DIRECTORY_SEPARATOR . $view;
	}
}