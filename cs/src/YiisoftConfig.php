<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\cs;

/**
 * Basic rules used by Yii 2 official packages.
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 * @since 2.0.0
 */
final class YiisoftConfig extends YiiConfig
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('yiisoft-cs-config');

        $header = <<<'HEADER'
@link http://www.yiiframework.com/
@copyright Copyright (c) 2008 Yii Software LLC
@license http://www.yiiframework.com/license/
HEADER;

        $this->mergeRules([
            'header_comment' => [
                'header' => $header,
                'commentType' => 'PHPDoc',
                'separate' => 'bottom',
            ],
        ]);
    }
}
