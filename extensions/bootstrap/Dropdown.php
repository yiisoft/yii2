<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Dropdown renders a Bootstrap dropdown menu component.
 *
 * @see http://getbootstrap.com/javascript/#dropdowns
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Dropdown extends Widget
{
    /**
     * @var array list of menu items in the dropdown. Each array element can be either an HTML string,
     * or an array representing a single menu with the following structure:
     *
     * - label: string, required, the label of the item link
     * - url: string, optional, the url of the item link. Defaults to "#".
     * - visible: boolean, optional, whether this menu item is visible. Defaults to true.
     * - linkOptions: array, optional, the HTML attributes of the item link.
     * - options: array, optional, the HTML attributes of the item.
     * - items: array, optional, the submenu items. The structure is the same as this property.
     *   Note that Bootstrap doesn't support dropdown submenu. You have to add your own CSS styles to support it.
     *
     * To insert divider use `<li role="presentation" class="divider"></li>`.
     */
    public $items = [];
    /**
     * @var boolean whether the labels for header items should be HTML-encoded.
     */
    public $encodeLabels = true;

    /**
     * @var array the HTML attributes for the widget container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    protected $_containerOptions = [];


    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, 'dropdown-menu');
        $this->_containerOptions = $this->options;
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo $this->renderItems($this->items);
    }

    /**
     * Renders menu items.
     * @param array $items the menu items to be rendered
     * @return string the rendering result.
     * @throws InvalidConfigException if the label option is not specified in one of the items.
     */
    protected function renderItems($items)
    {
        $lines = [];
        foreach ($items as $i => $item) {
            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }
            if (is_string($item)) {
                $lines[] = $item;
                continue;
            }
            if (!isset($item['label'])) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $options = ArrayHelper::getValue($item, 'options', []);
            $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);
            $linkOptions['tabindex'] = '-1';
            $content = Html::a($label, ArrayHelper::getValue($item, 'url', '#'), $linkOptions);
            if (!empty($item['items'])) {
                unset($this->_containerOptions['id']);
                $this->renderItems($item['items']);
                Html::addCssClass($options, 'dropdown-submenu');
            }
            $lines[] = Html::tag('li', $content, $options);
        }

        return Html::tag('ul', implode("\n", $lines), $this->_containerOptions);
    }
}
