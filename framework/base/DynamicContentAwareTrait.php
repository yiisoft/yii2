<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * DynamicContentAwareTrait implements common methods for classes
 * which support a [[View]] dynamic content feature.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.14
 */
trait DynamicContentAwareTrait
{
    /**
     * @var string[] a list of placeholders for dynamic content
     */
    private $_dynamicPlaceholders;

    /**
     * Returns the view object that can be used to render views or view files using dynamic contents.
     * @return View the view object that can be used to render views or view files.
     */
    abstract protected function getView();

    /**
     * {@inheritdoc}
     */
    public function getDynamicPlaceholders()
    {
        return $this->_dynamicPlaceholders;
    }

    /**
     * {@inheritdoc}
     */
    public function setDynamicPlaceholders($placeholders)
    {
        $this->_dynamicPlaceholders = $placeholders;
    }

    /**
     * {@inheritdoc}
     */
    public function addDynamicPlaceholder($name, $statements)
    {
        $this->_dynamicPlaceholders[$name] = $statements;
    }

    /**
     * Replaces placeholders in $content with results of evaluated dynamic statements.
     * @param string $content content to be parsed.
     * @param string[] $placeholders placeholders and their values.
     * @param bool $isRestoredFromCache whether content is going to be restored from cache.
     * @return string final content.
     */
    protected function updateDynamicContent($content, $placeholders, $isRestoredFromCache = false)
    {
        if (empty($placeholders) || !is_array($placeholders)) {
            return $content;
        }

        if (count($this->getView()->getDynamicContents()) === 0) {
            // outermost cache: replace placeholder with dynamic content
            foreach ($placeholders as $name => $statements) {
                $placeholders[$name] = $this->getView()->evaluateDynamicContent($statements);
            }
            $content = strtr($content, $placeholders);
        }
        if ($isRestoredFromCache) {
            foreach ($placeholders as $name => $statements) {
                $this->getView()->addDynamicPlaceholder($name, $statements);
            }
        }

        return $content;
    }
}
