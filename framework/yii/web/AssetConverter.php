<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;

/**
 * AssetConverter supports conversion of several popular script formats into JS or CSS scripts.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetConverter extends Component implements IAssetConverter
{
	/**
	 * @var array the commands that are used to perform the asset conversion.
	 * The keys are the asset file extension names, and the values are the corresponding
	 * target script types (either "css" or "js") and the commands used for the conversion.
	 */
	public $commands = array(
		'less' => array('css', 'lessc {from} {to}'),
		'scss' => array('css', 'sass {from} {to}'),
		'sass' => array('css', 'sass {from} {to}'),
		'styl' => array('js', 'stylus < {from} > {to}'),
	);

	/**
	 * Converts a given asset file into a CSS or JS file.
	 * @param string $asset the asset file path, relative to $basePath
	 * @param string $basePath the directory the $asset is relative to.
	 * @return string the converted asset file path, relative to $basePath.
	 */
	public function convert($asset, $basePath)
	{
		$pos = strrpos($asset, '.');
		if ($pos === false) {
			$ext = substr($asset, $pos + 1);
			if (isset($this->commands[$ext])) {
				list ($ext, $command) = $this->commands[$ext];
				$result = substr($asset, 0, $pos + 1) . $ext;
				if (@filemtime("$basePath/$result") < filemtime("$basePath/$asset")) {
					$output = array();
					$command = strtr($command, array(
						'{from}' => "$basePath/$asset",
						'{to}' => "$basePath/$result",
					));
					exec($command, $output);
					Yii::info("Converted $asset into $result: " . implode("\n", $output), __METHOD__);
				}
				return $result;
			}
		}
		return $asset;
	}
}
