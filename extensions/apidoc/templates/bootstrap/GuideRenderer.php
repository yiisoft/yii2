<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;

use Yii;
use yii\apidoc\helpers\ApiIndexer;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class GuideRenderer extends \yii\apidoc\templates\html\GuideRenderer
{
    use RendererTrait;

    public $layout = '@yii/apidoc/templates/bootstrap/layouts/guide.php';

    /**
     * @inheritDoc
     */
    public function render($files, $targetDir)
    {
        $types = array_merge($this->apiContext->classes, $this->apiContext->interfaces, $this->apiContext->traits);

        $extTypes = [];
        foreach ($this->extensions as $k => $ext) {
            $extType = $this->filterTypes($types, $ext);
            if (empty($extType)) {
                unset($this->extensions[$k]);
                continue;
            }
            $extTypes[$ext] = $extType;
        }

        parent::render($files, $targetDir);

        if ($this->controller !== null) {
            $this->controller->stdout('generating search index...');
        }

        $indexer = new ApiIndexer();
        $indexer->indexFiles(FileHelper::findFiles($targetDir, ['only' => ['*.html']]), $targetDir);
        $js = $indexer->exportJs();
        file_put_contents($targetDir . '/jssearch.index.js', $js);

        if ($this->controller !== null) {
            $this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }
    }
}
