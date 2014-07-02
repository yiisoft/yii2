<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\commands;

use yii\apidoc\components\BaseController;
use yii\apidoc\models\Context;
use yii\apidoc\renderers\GuideRenderer;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use Yii;

/**
 * This command can render documentation stored as markdown files such as the yii guide
 * or your own applications documentation setup.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class GuideController extends BaseController
{
    /**
     * @var string path or URL to the api docs to allow links to classes and properties/methods.
     */
    public $apiDocs;

    /**
     * Renders API documentation files
     * @param array $sourceDirs
     * @param string $targetDir
     * @return int
     */
    public function actionIndex(array $sourceDirs, $targetDir)
    {
        $renderer = $this->findRenderer($this->template);
        $targetDir = $this->normalizeTargetDir($targetDir);
        if ($targetDir === false || $renderer === false) {
            return 1;
        }

        $renderer->guideUrl = './';

        // setup reference to apidoc
        if ($this->apiDocs !== null) {
            $renderer->apiUrl = $this->apiDocs;
            $renderer->apiContext = $this->loadContext($this->apiDocs);
        } elseif (file_exists($targetDir . '/cache/apidoc.data')) {
            $renderer->apiUrl = './';
            $renderer->apiContext = $this->loadContext($targetDir);
        } else {
            $renderer->apiContext = new Context();
        }
        $this->updateContext($renderer->apiContext);

        // search for files to process
        if (($files = $this->searchFiles($sourceDirs)) === false) {
            return 1;
        }

        $renderer->controller = $this;
        $renderer->render($files, $targetDir);

        $this->stdout('Publishing images...');
        foreach ($sourceDirs as $source) {
            FileHelper::copyDirectory(rtrim($source, '/\\') . '/images', $targetDir . '/images');
        }
        $this->stdout('done.' . PHP_EOL, Console::FG_GREEN);

        // generate api references.txt
        $references = [];
        foreach ($files as $file) {
            $references[] = basename($file, '.md');
        }
        file_put_contents($targetDir . '/guide-references.txt', implode("\n", $references));
    }


    /**
     * @inheritdoc
     */
    protected function findFiles($path, $except = [])
    {
        $path = FileHelper::normalizePath($path);
        $options = [
            'only' => ['*.md'],
            'except' => $except,
        ];

        return FileHelper::findFiles($path, $options);
    }

    /**
     * @inheritdoc
     * @return GuideRenderer
     */
    protected function findRenderer($template)
    {
        $rendererClass = 'yii\\apidoc\\templates\\' . $template . '\\GuideRenderer';
        if (!class_exists($rendererClass)) {
            $this->stderr('Renderer not found.' . PHP_EOL);

            return false;
        }

        return new $rendererClass();
    }

    /**
     * @inheritdoc
     */
    public function options($actionId)
    {
        return array_merge(parent::options($actionId), ['apiDocs']);
    }
}
