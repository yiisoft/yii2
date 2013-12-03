<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{
	public $layout = 'main';
	/**
	 * @var  \yii\debug\Module
	 */
	public $module;
	/**
	 * @var array the summary data (e.g. URL, time)
	 */
	public $summary;

	public function actions()
	{
		$actions = [];
		foreach($this->module->panels as $panel) {
			$actions = array_merge($actions, $panel->actions);
		}
		return $actions;
	}

	public function actionIndex()
	{
		return $this->render('index', ['manifest' => $this->getManifest()]);
	}

	public function actionView($tag = null, $panel = null)
	{
		if ($tag === null) {
			$tags = array_keys($this->getManifest());
			$tag = reset($tags);
		}
		$this->loadData($tag);
		if (isset($this->module->panels[$panel])) {
			$activePanel = $this->module->panels[$panel];
		} else {
			$activePanel = $this->module->panels['request'];
		}
		return $this->render('view', [
			'tag' => $tag,
			'summary' => $this->summary,
			'manifest' => $this->getManifest(),
			'panels' => $this->module->panels,
			'activePanel' => $activePanel,
		]);
	}

	public function actionToolbar($tag)
	{
		$this->loadData($tag);
		return $this->renderPartial('toolbar', [
			'tag' => $tag,
			'panels' => $this->module->panels,
		]);
	}

	public function actionPhpinfo()
	{
		phpinfo();
	}

	private $_manifest;

	protected function getManifest()
	{
		if ($this->_manifest === null) {
			$indexFile = $this->module->dataPath . '/index.data';
			if (is_file($indexFile)) {
				$this->_manifest = array_reverse(unserialize(file_get_contents($indexFile)), true);
			} else {
				$this->_manifest = [];
			}
		}
		return $this->_manifest;
	}

	public function loadData($tag)
	{
		$manifest = $this->getManifest();
		if (isset($manifest[$tag])) {
			$dataFile = $this->module->dataPath . "/$tag.data";
			$data = unserialize(file_get_contents($dataFile));
			foreach ($this->module->panels as $id => $panel) {
				if (isset($data[$id])) {
					$panel->tag = $tag;
					$panel->load($data[$id]);
				} else {
					// remove the panel since it has not received any data
					unset($this->module->panels[$id]);
				}
			}
			$this->summary = $data['summary'];
		} else {
			throw new NotFoundHttpException("Unable to find debug data tagged with '$tag'.");
		}
	}
}
