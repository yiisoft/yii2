<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\renderers;

use Yii;
use yii\apidoc\helpers\IndexFileAnalyzer;

/**
 * Base class for all Guide documentation renderers
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class GuideRenderer extends BaseRenderer
{
    /**
     * Render markdown files
     *
     * @param array $files list of markdown files to render
     * @param $targetDir
     */
    abstract public function render($files, $targetDir);


    /**
     * Loads guide structure from a set of files
     * @param array $files
     * @return array
     */
    protected function loadGuideStructure($files)
    {
        $chapters = [];
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            if (basename($file) == 'README.md') {
                $indexAnalyzer = new IndexFileAnalyzer();
                $chapters = $indexAnalyzer->analyze($contents);
                break;
            }
            if (preg_match("/^(.*)\n=+/", $contents, $matches)) {
                $headlines[$file] = $matches[1];
            } else {
                $headlines[$file] = basename($file);
            }

        }
        return $chapters;
    }
}
