<?php
/**
 * Composite view renderer class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\renderers;
use Yii;
use yii\base\View;
use yii\base\ViewRenderer;

/**
 * CompositeViewRenderer allows you to use multiple view renderers in a single
 * application.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class CompositeViewRenderer extends ViewRenderer
{
	/**
	 * @var array a config array with the view renderer objects or the configuration arrays for
	 * creating the view renderers indexed by file extensions.
	 */
	public $renderers = array();

	/**
	 * Renders a view file.
	 *
	 * This method is invoked by [[View]] whenever it tries to render a view.
	 * Child classes must implement this method to render the given view file.
	 *
	 * @param View $view the view object used for rendering the file.
	 * @param string $file the view file.
	 * @param array $params the parameters to be passed to the view file.
	 *
	 * @return string the rendering result
	 */
	public function render($view, $file, $params)
	{
		$ext = pathinfo($file, PATHINFO_EXTENSION);

		if($ext === 'php' || !isset($this->renderers[$ext])) {
			return $view->renderPhpFile($file, $params);
		}
		else {
			if (is_array($this->renderers[$ext])) {
				$this->renderers[$ext] = Yii::createObject($this->renderers[$ext]);
			}
			return $this->renderers[$ext]->render($view, $file, $params);
		}
	}
}