<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\html;

use yii\apidoc\helpers\ApiMarkdown;
use yii\console\Controller;
use yii\helpers\Console;
use yii\apidoc\renderers\GuideRenderer as BaseGuideRenderer;
use Yii;
use yii\helpers\Html;
use yii\web\AssetManager;
use yii\web\View;

/**
 *
 * @property View $view The view instance. This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class GuideRenderer extends BaseGuideRenderer
{
    public $pageTitle;
    public $layout;

    /**
     * @var View
     */
    private $_view;
    private $_targetDir;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->pageTitle === null) {
            $this->pageTitle = 'Yii Framework 2.0 API Documentation'; // TODO guess page title
        }
    }

    /**
     * @return View the view instance
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = new View();
            $assetPath = Yii::getAlias($this->_targetDir) . '/assets';
            if (!is_dir($assetPath)) {
                mkdir($assetPath);
            }
            $this->_view->assetManager = new AssetManager([
                'basePath' => $assetPath,
                'baseUrl' => './assets',
            ]);
        }

        return $this->_view;
    }

    /**
     * Renders a set of files given into target directory.
     *
     * @param array $files
     * @param string $targetDir
     */
    public function render($files, $targetDir)
    {
        $this->_targetDir = $targetDir;

        $fileCount = count($files) + 1;
        if ($this->controller !== null) {
            Console::startProgress(0, $fileCount, 'Rendering markdown files: ', false);
        }
        $done = 0;
        $fileData = [];
        $chapters = $this->loadGuideStructure($files);
        foreach ($files as $file) {
            $fileData[$file] = file_get_contents($file);
            if (basename($file) == 'README.md') {
                continue; // to not add index file to nav
            }
        }

        foreach ($fileData as $file => $content) {
            $output = ApiMarkdown::process($content); // TODO generate links to yiiframework.com by default
            $output = $this->fixMarkdownLinks($output);
            if ($this->layout !== false) {
                $params = [
                    'chapters' => $chapters,
                    'currentFile' => $file,
                    'content' => $output,
                ];
                $output = $this->getView()->renderFile($this->layout, $params, $this);
            }
            $fileName = $this->generateGuideFileName($file);
            file_put_contents($targetDir . '/' . $fileName, $output);

            if ($this->controller !== null) {
                Console::updateProgress(++$done, $fileCount);
            }
        }
        if ($this->controller !== null) {
            Console::updateProgress(++$done, $fileCount);
            Console::endProgress(true);
            $this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }
    }

    /**
     * Given markdown file name generates resulting html file name
     * @param string $file markdown file name
     * @return string
     */
    protected function generateGuideFileName($file)
    {
        return $this->guidePrefix . basename($file, '.md') . '.html';
    }

    public function getGuideReferences()
    {
        // TODO implement for api docs
//		$refs = [];
//		foreach ($this->markDownFiles as $file) {
//			$refName = 'guide-' . basename($file, '.md');
//			$refs[$refName] = ['url' => $this->generateGuideFileName($file)];
//		}
//		return $refs;
    }

    /**
     * Adds guide name to link URLs in markdown
     * @param string $content
     * @return string
     */
    protected function fixMarkdownLinks($content)
    {
        $content = preg_replace('/href\s*=\s*"([^"\/]+)\.md(#.*)?"/i', 'href="' . $this->guidePrefix . '\1.html\2"', $content);

        return $content;
    }

    /**
     * @inheritdoc
     */
    protected function generateLink($text, $href, $options = [])
    {
        $options['href'] = $href;

        return Html::a($text, null, $options);
    }

    /**
     * @inheritdoc
     */
    public function generateApiUrl($typeName)
    {
        return rtrim($this->apiUrl, '/') . '/' . strtolower(str_replace('\\', '-', $typeName)) . '.html';
    }
}
