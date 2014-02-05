<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\components;

use yii\gii\Generator;
use yii\helpers\Json;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveField extends \yii\widgets\ActiveField
{
	/**
	 * @var Generator
	 */
	public $model;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$stickyAttributes = $this->model->stickyAttributes();
		if (in_array($this->attribute, $stickyAttributes)) {
			$this->sticky();
		}
		$hints = $this->model->hints();
		if (isset($hints[$this->attribute])) {
			$this->hint($hints[$this->attribute]);
		}
		$autoCompleteData = $this->model->autoCompleteData();
		if (isset($autoCompleteData[$this->attribute])) {
			if (is_callable($autoCompleteData[$this->attribute])) {
				$this->autoComplete(call_user_func($autoCompleteData[$this->attribute]));
			} else {
				$this->autoComplete($autoCompleteData[$this->attribute]);
			}
		}
	}

	/**
	 * Makes field remember its value between page reloads
	 * @return static the field object itself
	 */
	public function sticky()
	{
		$this->options['class'] .= ' sticky';
		return $this;
	}

	/**
	 * Makes field auto completable
	 * @param array $data auto complete data (array of callables or scalars)
	 * @return static the field object itself
	 */
	public function autoComplete($data)
	{
		static $counter = 0;
		$this->inputOptions['class'] .= ' typeahead-' . (++$counter);
		foreach($data as &$item) {
			$item = array('word' => $item);
		}
		$this->form->getView()->registerJs("
var datum = new Bloodhound({
	datumTokenizer: function(d){return Bloodhound.tokenizers.whitespace(d.word);},
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	local: " . Json::encode($data) . "
});
datum.initialize();
jQuery('.typeahead-{$counter}').typeahead(null,{displayKey: 'word', source: datum.ttAdapter()});");
		return $this;
	}
}
