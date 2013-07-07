<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{
	/** @var  \yii\debug\Module */
	public $module;
	public $layout = 'main';

	public function actionIndex($tag = null, $panel = null)
	{
		if ($tag === null) {
			$tags = array_keys($this->getManifest());
			$tag = end($tags);
		}
		$meta = $this->loadData($tag);
		if (isset($this->module->panels[$panel])) {
			$activePanel = $this->module->panels[$panel];
		} else {
			$activePanel = $this->module->panels['request'];
		}
		return $this->render('index', array(
			'tag' => $tag,
			'meta' => $meta,
			'manifest' => $this->getManifest(),
			'panels' => $this->module->panels,
			'activePanel' => $activePanel,
		));
	}

	public function actionToolbar($tag)
	{
		$this->loadData($tag);
		return $this->renderPartial('toolbar', array(
			'tag' => $tag,
			'panels' => $this->module->panels,
		));
	}

	public function actionPhpinfo()
	{
		phpinfo();
	}

	private $_manifest;

	protected function getManifest()
	{
		if ($this->_manifest === null) {
			$indexFile = $this->module->dataPath . '/index.json';
			if (is_file($indexFile)) {
				$this->_manifest = json_decode(file_get_contents($indexFile), true);
			} else {
				$this->_manifest = array();
			}
			if (count($this->_manifest) > $this->module->historySize) {
				$n = count($this->_manifest) - $this->module->historySize;
				foreach (array_keys($this->_manifest) as $tag) {
					$file = $this->module->dataPath . "/$tag.json";
					@unlink($file);
					unset($this->_manifest[$tag]);
					if (--$n <= 0) {
						break;
					}
				}
				file_put_contents($indexFile, json_encode($this->_manifest));
			}
		}
		return $this->_manifest;
	}

	protected function loadData($tag)
	{
		$manifest = $this->getManifest();
		if (isset($manifest[$tag])) {
			$dataFile = $this->module->dataPath . "/$tag.json";
			$data = json_decode(file_get_contents($dataFile), true);
			foreach ($this->module->panels as $id => $panel) {
				if (isset($data[$id])) {
					$panel->load($data[$id]);
				} else {
					// remove the panel since it has not received any data
					unset($this->module->panels[$id]);
				}
			}
			return $manifest[$tag];
		} else {
			throw new HttpException(404, "Unable to find debug data tagged with '$tag'.");
		}
	}
}
