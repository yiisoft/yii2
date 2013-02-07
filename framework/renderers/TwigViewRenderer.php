<?php
/**
 * Twig view renderer class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\renderers;

use \yii\base\View;
use \yii\base\ViewRenderer;

/**
 * TwigViewRenderer allows you to use Twig templates in views.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class TwigViewRenderer extends ViewRenderer
{
	public $twigDir = '@app/vendors/Twig';
	public $cacheDir = '@app/runtime/Twig/cache';
	public $fileExtension = 'twig';

	/**
	 * @var array options
	 * @see http://twig.sensiolabs.org/doc/api.html#environment-options
	 */
	public $options = array();

	/**
	 * @var \Twig_Environment
	 */
	protected $_twig;

	public function init()
	{
		\Yii::setAlias('@Twig', $this->twigDir);

		$loader = new \Twig_Loader_String();

		$this->_twig = new \Twig_Environment($loader, array_merge(array(
			'cache' => \Yii::getAlias($this->cacheDir),
		), $this->options));
	}

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
		if($ext === $this->fileExtension) {
			return $this->_twig->render(file_get_contents($file), $params);
		}
		else {
			return $view->renderPhpFile($file, $params);
		}
	}
}
