<?php

namespace yii\base;

/**
 * Created by JetBrains PhpStorm.
 * User: qiang
 * Date: 2/1/13
 * Time: 12:43 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class ViewRenderer extends Component
{
	/**
	 * @var boolean whether to store the parsing results in the application's
	 * runtime directory. Defaults to true. If false, the parsing results will
	 * be saved as files under the same directory as the source view files and the
	 * file names will be the source file names appended with letter 'c'.
	 */
	public $useRuntimePath = true;
	/**
	 * @var integer the chmod permission for temporary directories and files
	 * generated during parsing. Defaults to 0755 (owner rwx, group rx and others rx).
	 */
	public $filePermission = 0755;
	/**
	 * @var string the extension name of the view file. Defaults to '.php'.
	 */
	public $fileExtension = '.php';

	/**
	 * Parses the source view file and saves the results as another file.
	 * @param string $sourceFile the source view file path
	 * @param string $viewFile the resulting view file path
	 */
	abstract protected function generateViewFile($sourceFile, $viewFile);

	/**
	 * Renders a view file.
	 * This method is required by {@link IViewRenderer}.
	 * @param CBaseController $context the controller or widget who is rendering the view file.
	 * @param string $sourceFile the view file path
	 * @param mixed $data the data to be passed to the view
	 * @param boolean $return whether the rendering result should be returned
	 * @return mixed the rendering result, or null if the rendering result is not needed.
	 */
	public function renderFile($context, $sourceFile, $data, $return)
	{
		if (!is_file($sourceFile) || ($file = realpath($sourceFile)) === false) {
			throw new CException(Yii::t('yii', 'View file "{file}" does not exist.', array('{file}' => $sourceFile)));
		}
		$viewFile = $this->getViewFile($sourceFile);
		if (@filemtime($sourceFile) > @filemtime($viewFile)) {
			$this->generateViewFile($sourceFile, $viewFile);
			@chmod($viewFile, $this->filePermission);
		}
		return $context->renderInternal($viewFile, $data, $return);
	}

	/**
	 * Generates the resulting view file path.
	 * @param string $file source view file path
	 * @return string resulting view file path
	 */
	protected function getViewFile($file)
	{
		if ($this->useRuntimePath) {
			$crc = sprintf('%x', crc32(get_class($this) . Yii::getVersion() . dirname($file)));
			$viewFile = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $crc . DIRECTORY_SEPARATOR . basename($file);
			if (!is_file($viewFile)) {
				@mkdir(dirname($viewFile), $this->filePermission, true);
			}
			return $viewFile;
		} else {
			return $file . 'c';
		}
	}
}
