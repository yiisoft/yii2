<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Tabs renders a tabs jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Tabs::widget([
 *     'items' => [
 *         [
 *             'label' => 'Tab one',
 *             'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
 *         ],
 *         [
 *             'label' => 'Tab two',
 *             'content' => 'Sed non urna. Phasellus eu ligula. Vestibulum sit amet purus...',
 *             'options' => ['tag' => 'div'],
 *             'headerOptions' => ['class' => 'my-class'],
 *         ],
 *         [
 *             'label' => 'Tab with custom id',
 *             'content' => 'Morbi tincidunt, dui sit amet facilisis feugiat...',
 *             'options' => ['id' => 'my-tab'],
 *         ],
 *         [
 *             'label' => 'Ajax tab',
 *             'url' => ['ajax/content'],
 *         ],
 *     ),
 *     'options' => ['tag' => 'div'],
 *     'itemOptions' => ['tag' => 'div'],
 *     'headerOptions' => ['class' => 'my-class'],
 *     'clientOptions' => ['collapsible' => false],
 * ]);
 * ```
 *
 * @see http://api.jqueryui.com/tabs/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Tabs extends Widget
{
    /**
     * @var array the HTML attributes for the widget container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "div", the tag name of the container tag of this widget.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var array list of tab items. Each item can be an array of the following structure:
     *
     * - label: string, required, specifies the header link label. When [[encodeLabels]] is true, the label
     *   will be HTML-encoded.
     * - content: string, the content to show when corresponding tab is clicked. Can be omitted if url is specified.
     * - url: mixed, mixed, optional, the url to load tab contents via AJAX. It is required if no content is specified.
     * - template: string, optional, the header link template to render the header link. If none specified
     *   [[linkTemplate]] will be used instead.
     * - options: array, optional, the HTML attributes of the header.
     * - headerOptions: array, optional, the HTML attributes for the header container tag.
     */
    public $items = [];
    /**
     * @var array list of HTML attributes for the item container tags. This will be overwritten
     * by the "options" set in individual [[items]]. The following special options are recognized:
     *
     * - tag: string, defaults to "div", the tag name of the item container tags.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $itemOptions = [];
    /**
     * @var array list of HTML attributes for the header container tags. This will be overwritten
     * by the "headerOptions" set in individual [[items]].
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $headerOptions = [];
    /**
     * @var string the default header template to render the link.
     */
    public $linkTemplate = '<a href="{url}">{label}</a>';
    /**
     * @var boolean whether the labels for header items should be HTML-encoded.
     */
    public $encodeLabels = true;

    /**
     * Renders the widget.
     */
    public function run()
    {
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        echo Html::beginTag($tag, $options) . "\n";
        echo $this->renderItems() . "\n";
        echo Html::endTag($tag) . "\n";
        $this->registerWidget('tabs', TabsAsset::className());
    }

    /**
     * Renders tab items as specified on [[items]].
     * @return string the rendering result.
     * @throws InvalidConfigException.
     */
    protected function renderItems()
    {
        $headers = [];
        $items = [];
        foreach ($this->items as $n => $item) {
            if (!isset($item['label'])) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            if (isset($item['url'])) {
                $url = Url::to($item['url']);
            } else {
                if (!isset($item['content'])) {
                    throw new InvalidConfigException("The 'content' or 'url' option is required.");
                }
                $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
                $tag = ArrayHelper::remove($options, 'tag', 'div');
                if (!isset($options['id'])) {
                    $options['id'] = $this->options['id'] . '-tab' . $n;
                }
                $url = '#' . $options['id'];
                $items[] = Html::tag($tag, $item['content'], $options);
            }
            $headerOptions = array_merge($this->headerOptions, ArrayHelper::getValue($item, 'headerOptions', []));
            $template = ArrayHelper::getValue($item, 'template', $this->linkTemplate);
            $headers[] = Html::tag('li', strtr($template, [
                '{label}' => $this->encodeLabels ? Html::encode($item['label']) : $item['label'],
                '{url}' => $url,
            ]), $headerOptions);
        }

        return Html::tag('ul', implode("\n", $headers)) . "\n" . implode("\n", $items);
    }
}
