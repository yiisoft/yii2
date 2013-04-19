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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetConverter extends Component implements IAssetConverter
{
	public $commands = array(
		'less' => array('css', 'lessc %s %s'),
		'scss' => array('css', 'sass %s %s'),
		'sass' => array('css', 'sass %s %s'),
		'styl' => array('js', 'stylus < %s > %s'),
	);

	public function convert($asset, $basePath, $baseUrl)
	{
		$pos = strrpos($asset, '.');
		if ($pos !== false) {
			$ext = substr($asset, $pos + 1);
			if (isset($this->commands[$ext])) {
				list ($ext, $command) = $this->commands[$ext];
				$result = substr($asset, 0, $pos + 1) . $ext;
				if (@filemtime("$basePath/$result") < filemtime("$basePath/$asset")) {
					$output = array();
					$command = sprintf($command, "$basePath/$asset", "$basePath/$result");
					exec($command, $output);
					Yii::info("Converted $asset into $result: " . implode("\n", $output), __METHOD__);
					return "$baseUrl/$result";
				}
			}
		}
		return "$baseUrl/$asset";
	}
}