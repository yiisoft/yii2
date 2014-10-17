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
 * Collapse renders an accordion bootstrap javascript component.
 *
 * For example:
 *
 * ```php
 * echo Collapse::widget([
 *     'items' => [
 *         // equivalent to the above
 *         [
 *             'label' => 'Collapsible Group Item #1',
 *             'content' => 'Anim pariatur cliche...',
 *             // open its content by default
 *             'contentOptions' => ['class' => 'in']
 *         ],
 *         // another group item
 *         [
 *             'label' => 'Collapsible Group Item #1',
 *             'content' => 'Anim pariatur cliche...',
 *             'contentOptions' => [...],
 *             'options' => [...],
 *         ],
 *     ]
 * ]);
 * ```
 *
 * @see http://getbootstrap.com/javascript/#collapse
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Collapse extends Widget
{
    /**
     * @var array list of groups in the collapse widget. Each array element represents a single
     * group with the following structure:
     *
     * - label: string, required, the group header label.
     * - encode: boolean, optional, whether this label should be HTML-encoded. This param will override
     *   global `$this->encodeLabels` param.
     * - content: string, required, the content (HTML) of the group
     * - options: array, optional, the HTML attributes of the group
     * - contentOptions: optional, the HTML attributes of the group's content
     *
     * ```
     */
    public $items = [];

    /**
     * @var boolean whether the labels for header items should be HTML-encoded.
     */
    public $encodeLabels = true;


    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, 'panel-group');
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo Html::beginTag('div', $this->options) . "\n";
        echo $this->renderItems() . "\n";
        echo Html::endTag('div') . "\n";
        $this->registerPlugin('collapse');
    }

    /**
     * Renders collapsible items as specified on [[items]].
     * @throws InvalidConfigException if label isn't specified
     * @return string the rendering result
     */
    public function renderItems()
    {
        $items = [];
        $index = 0;
        foreach ($this->items as $item) {
            if (!isset($item['label'])) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            $header = $item['label'];
            $options = ArrayHelper::getValue($item, 'options', []);
            Html::addCssClass($options, 'panel panel-default');
            $items[] = Html::tag('div', $this->renderItem($header, $item, ++$index), $options);
        }

        return implode("\n", $items);
    }

    /**
     * Renders a single collapsible item group
     * @param string $header a label of the item group [[items]]
     * @param array $item a single item from [[items]]
     * @param integer $index the item index as each item group content must have an id
     * @return string the rendering result
     * @throws InvalidConfigException
     */
    public function renderItem($header, $item, $index)
    {
        if (isset($item['content'])) {
            $id = $this->options['id'] . '-collapse' . $index;
            $options = ArrayHelper::getValue($item, 'contentOptions', []);
            $options['id'] = $id;
            Html::addCssClass($options, 'panel-collapse collapse');

            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            if ($encodeLabel) {
                $header = Html::encode($header);
            }

            $headerToggle = Html::a($header, '#' . $id, [
                    'class' => 'collapse-toggle',
                    'data-toggle' => 'collapse',
                    'data-parent' => '#' . $this->options['id']
                ]) . "\n";

            $header = Html::tag('h4', $headerToggle, ['class' => 'panel-title']);

            $content = Html::tag('div', $item['content'], ['class' => 'panel-body']) . "\n";
        } else {
            throw new InvalidConfigException('The "content" option is required.');
        }
        $group = [];

        $group[] = Html::tag('div', $header, ['class' => 'panel-heading']);
        $group[] = Html::tag('div', $content, $options);

        return implode("\n", $group);
    }
}
