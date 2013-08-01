<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets\grid;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CheckboxColumn extends Column
{
	public $checked;
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will
	 * determine if checkbox for each data cell is disabled. In this expression, you can use the following variables:
	 * <ul>
	 *   <li><code>$row</code> the row number (zero-based)</li>
	 *   <li><code>$data</code> the data model for the row</li>
	 *   <li><code>$this</code> the column object</li>
	 * </ul>
	 * The PHP expression will be evaluated using {@link evaluateExpression}.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
	 *
	 * Note that expression result will overwrite value set with <code>checkBoxHtmlOptions['disabled']</code>.
	 * @since 1.1.13
	 */
	public $disabled;
	/**
	 * @var array the HTML options for the data cell tags.
	 */
	public $htmlOptions = array('class' => 'checkbox-column');
	/**
	 * @var array the HTML options for the header cell tag.
	 */
	public $headerHtmlOptions = array('class' => 'checkbox-column');
	/**
	 * @var array the HTML options for the footer cell tag.
	 */
	public $footerHtmlOptions = array('class' => 'checkbox-column');
	/**
	 * @var array the HTML options for the checkboxes.
	 */
	public $checkBoxHtmlOptions = array();
	/**
	 * @var integer the number of rows that can be checked.
	 * Possible values:
	 * <ul>
	 * <li>0 - the state of the checkbox cannot be changed (read-only mode)</li>
	 * <li>1 - only one row can be checked. Checking a checkbox has nothing to do with selecting the row</li>
	 * <li>2 or more - multiple checkboxes can be checked. Checking a checkbox has nothing to do with selecting the row</li>
	 * <li>null - {@link CGridView::selectableRows} is used to control how many checkboxes can be checked.
	 * Checking a checkbox will also select the row.</li>
	 * </ul>
	 * You may also call the JavaScript function <code>$(gridID).yiiGridView('getChecked', columnID)</code>
	 * to retrieve the key values of the checked rows.
	 * @since 1.1.6
	 */
	public $selectableRows = null;
	/**
	 * @var string the template to be used to control the layout of the header cell.
	 * The token "{item}" is recognized and it will be replaced with a "check all" checkbox.
	 * By default if in multiple checking mode, the header cell will display an additional checkbox,
	 * clicking on which will check or uncheck all of the checkboxes in the data cells.
	 * See {@link selectableRows} for more details.
	 * @since 1.1.11
	 */
	public $headerTemplate = '{item}';

	/**
	 * Initializes the column.
	 * This method registers necessary client script for the checkbox column.
	 */
	public function init()
	{
		if (isset($this->checkBoxHtmlOptions['name'])) {
			$name = $this->checkBoxHtmlOptions['name'];
		} else {
			$name = $this->id;
			if (substr($name, -2) !== '[]') {
				$name .= '[]';
			}
			$this->checkBoxHtmlOptions['name'] = $name;
		}
		$name = strtr($name, array('[' => "\\[", ']' => "\\]"));

		if ($this->selectableRows === null) {
			if (isset($this->checkBoxHtmlOptions['class'])) {
				$this->checkBoxHtmlOptions['class'] .= ' select-on-check';
			} else {
				$this->checkBoxHtmlOptions['class'] = 'select-on-check';
			}
			return;
		}

		$cball = $cbcode = '';
		if ($this->selectableRows == 0) {
			//.. read only
			$cbcode = "return false;";
		} elseif ($this->selectableRows == 1) {
			//.. only one can be checked, uncheck all other
			$cbcode = "jQuery(\"input:not(#\"+this.id+\")[name='$name']\").prop('checked',false);";
		} elseif (strpos($this->headerTemplate, '{item}') !== false) {
			//.. process check/uncheck all
			$cball = <<<CBALL
jQuery(document).on('click','#{$this->id}_all',function() {
	var checked=this.checked;
	jQuery("input[name='$name']:enabled").each(function() {this.checked=checked;});
});

CBALL;
			$cbcode = "jQuery('#{$this->id}_all').prop('checked', jQuery(\"input[name='$name']\").length==jQuery(\"input[name='$name']:checked\").length);";
		}

		if ($cbcode !== '') {
			$js = $cball;
			$js .= <<<EOD
jQuery(document).on('click', "input[name='$name']", function() {
	$cbcode
});
EOD;
			Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id, $js);
		}
	}

	/**
	 * Renders the header cell content.
	 * This method will render a checkbox in the header when {@link selectableRows} is greater than 1
	 * or in case {@link selectableRows} is null when {@link CGridView::selectableRows} is greater than 1.
	 */
	protected function renderHeaderCellContent()
	{
		if (trim($this->headerTemplate) === '') {
			echo $this->grid->blankDisplay;
			return;
		}

		$item = '';
		if ($this->selectableRows === null && $this->grid->selectableRows > 1) {
			$item = CHtml::checkBox($this->id . '_all', false, array('class' => 'select-on-check-all'));
		} elseif ($this->selectableRows > 1) {
			$item = CHtml::checkBox($this->id . '_all', false);
		} else {
			ob_start();
			parent::renderHeaderCellContent();
			$item = ob_get_clean();
		}

		echo strtr($this->headerTemplate, array(
			'{item}' => $item,
		));
	}

	/**
	 * Renders the data cell content.
	 * This method renders a checkbox in the data cell.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
	 */
	protected function renderDataCellContent($row, $data)
	{
		if ($this->value !== null) {
			$value = $this->evaluateExpression($this->value, array('data' => $data, 'row' => $row));
		} elseif ($this->name !== null) {
			$value = CHtml::value($data, $this->name);
		} else {
			$value = $this->grid->dataProvider->keys[$row];
		}

		$checked = false;
		if ($this->checked !== null) {
			$checked = $this->evaluateExpression($this->checked, array('data' => $data, 'row' => $row));
		}

		$options = $this->checkBoxHtmlOptions;
		if ($this->disabled !== null) {
			$options['disabled'] = $this->evaluateExpression($this->disabled, array('data' => $data, 'row' => $row));
		}

		$name = $options['name'];
		unset($options['name']);
		$options['value'] = $value;
		$options['id'] = $this->id . '_' . $row;
		echo CHtml::checkBox($name, $checked, $options);
	}
}
