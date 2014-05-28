<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\pdf;

use cebe\markdown\latex\GithubMarkdown;
use Yii;
use yii\apidoc\helpers\ApiIndexer;
use yii\apidoc\helpers\IndexFileAnalyzer;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class GuideRenderer extends \yii\apidoc\templates\html\GuideRenderer
{
    /**
     * @inheritDoc
     */
    public function render($files, $targetDir)
    {
//        $types = array_merge($this->apiContext->classes, $this->apiContext->interfaces, $this->apiContext->traits);
//
//        $extTypes = [];
//        foreach ($this->extensions as $k => $ext) {
//            $extType = $this->filterTypes($types, $ext);
//            if (empty($extType)) {
//                unset($this->extensions[$k]);
//                continue;
//            }
//            $extTypes[$ext] = $extType;
//        }

        $fileCount = count($files) + 1;
        if ($this->controller !== null) {
            Console::startProgress(0, $fileCount, 'Rendering markdown files: ', false);
        }
        $done = 0;
        $fileData = [];
        $chapters = [];
        foreach ($files as $file) {
            if (basename($file) == 'README.md') {
                $indexAnalyzer = new IndexFileAnalyzer();
                $chapters = $indexAnalyzer->analyze(file_get_contents($file));
                continue; // to not add index file to nav
            }
            if (basename($file) == 'tutorial-i18n.md') {
                continue; // TODO avoid i18n tut because of non displayable characters right now. need to fix it.
            }
            $fileData[basename($file)] = file_get_contents($file);
//            if (preg_match("/^(.*)\n=+/", $fileData[$file], $matches)) {
//                $headlines[$file] = $matches[1];
//            } else {
//                $headlines[$file] = basename($file);
//            }
        }

        $md = new GithubMarkdown();
        $output = '';
        foreach ($chapters as $chapter) {

            $output .= '\chapter{' . $chapter['headline'] . "}\n";
            foreach($chapter['content'] as $content) {
                if (isset($fileData[$content['file']])) {
                    $md->labelPrefix = $content['file'] . '#';
                    $output .= '\label{'. $content['file'] . '}';
                    $output .= $md->parse($fileData[$content['file']]) . "\n\n";
                } else {
                    $output .= '\newpage';
                    $output .= '\label{'. $content['file'] . '}';
                    $output .= '\textbf{Error: not existing file: '.$content['file'].'}\newpage'."\n";
                }

                if ($this->controller !== null) {
                    Console::updateProgress(++$done, $fileCount);
                }
            }
        }
        file_put_contents($targetDir . '/guide.tex', $output);
        copy(__DIR__ . '/main.tex', $targetDir . '/main.tex');
        copy(__DIR__ . '/Makefile', $targetDir . '/Makefile');

        if ($this->controller !== null) {
            Console::updateProgress(++$done, $fileCount);
            Console::endProgress(true);
            $this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }

        echo "\nnow run `make pdf` in $targetDir (you need pdflatex to compile pdf file)\n\n";
    }
}
