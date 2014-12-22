<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\pdf;

use Yii;
use yii\apidoc\helpers\ApiMarkdownLaTeX;
use yii\helpers\Console;

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
        $chapters = $this->loadGuideStructure($files);
        foreach ($files as $file) {
            $fileData[basename($file)] = file_get_contents($file);
//            if (preg_match("/^(.*)\n=+/", $fileData[$file], $matches)) {
//                $headlines[$file] = $matches[1];
//            } else {
//                $headlines[$file] = basename($file);
//            }
        }

        $md = new ApiMarkdownLaTeX();
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
