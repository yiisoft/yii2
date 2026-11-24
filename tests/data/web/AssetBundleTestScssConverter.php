<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\web;

use yii\web\AssetConverter;

/**
 * Simulates SCSS conversion without requiring an external compiler.
 */
class AssetBundleTestScssConverter extends AssetConverter
{
    protected function runCommand($command, $basePath, $asset, $result)
    {
        $content = file_get_contents("{$basePath}/{$asset}");

        if ($content === '@import "colors";') {
            $content = file_get_contents("{$basePath}/scss/_colors.scss");
        }

        return file_put_contents("{$basePath}/{$result}", $content) !== false;
    }
}
