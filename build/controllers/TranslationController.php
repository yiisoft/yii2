<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use DirectoryIterator;
use Yii;
use yii\console\Controller;
use yii\helpers\Html;

/**
 * TranslationController handles tasks related to framework translations.
 *
 * build translation "../docs/guide" "../docs/guide-ru" "Russian guide translation report" > report_guide_ru.html
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class TranslationController extends Controller
{
    public $defaultAction = 'report';

    /**
     * Creates a report about documentation updates since last update of same named translations.
     *
     * @param string $sourcePath the directory where the original documentation files are
     * @param string $translationPath the directory where the translated documentation files are
     * @param string $title custom title to use for report
     */
    public function actionReport($sourcePath, $translationPath, $title = 'Translation report')
    {
        $sourcePath = trim($sourcePath, '/\\');
        $translationPath = trim($translationPath, '/\\');

        $results = [];

        $dir = new DirectoryIterator($sourcePath);
        foreach ($dir as $fileinfo) {
            /* @var $fileinfo DirectoryIterator */
            if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
                $translatedFilePath = $translationPath . '/' . $fileinfo->getFilename();
                $sourceFilePath = $sourcePath . '/' . $fileinfo->getFilename();

                $errors = $this->checkFiles($translatedFilePath);
                $diff = empty($errors) ? $this->getDiff($translatedFilePath, $sourceFilePath) : '';
                if (!empty($diff)) {
                    $errors[] = 'Translation outdated.';
                }

                $result = [
                    'errors' => $errors,
                    'diff' => $diff,
                ];

                $results[$fileinfo->getFilename()] = $result;
            }
        }

        // checking if there are obsolete translation files
        $dir = new DirectoryIterator($translationPath);
        foreach ($dir as $fileinfo) {
            /* @var $fileinfo \DirectoryIterator */
            if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
                $translatedFilePath = $translationPath . '/' . $fileinfo->getFilename();

                $errors = $this->checkFiles(null, $translatedFilePath);
                if (!empty($errors)) {
                    $results[$fileinfo->getFilename()]['errors'] = $errors;
                }
            }
        }

        echo $this->renderFile(__DIR__ . '/views/translation/report_html.php', [
            'results' => $results,
            'sourcePath' => $sourcePath,
            'translationPath' => $translationPath,
            'title' => $title,
        ]);
    }

    /**
     * Checks for files existence.
     *
     * @param string $translatedFilePath
     * @param string $sourceFilePath
     * @return array errors
     */
    protected function checkFiles($translatedFilePath = null, $sourceFilePath = null)
    {
        $errors = [];
        if ($translatedFilePath !== null && !file_exists($translatedFilePath)) {
            $errors[] = 'Translation does not exist.';
        }

        if ($sourceFilePath !== null && !file_exists($sourceFilePath)) {
            $errors[] = 'Source does not exist.';
        }

        return $errors;
    }

    /**
     * Getting DIFF from git.
     *
     * @param string $translatedFilePath path pointing to translated file
     * @param string $sourceFilePath path pointing to original file
     * @return string DIFF
     */
    protected function getDiff($translatedFilePath, $sourceFilePath)
    {
        $lastTranslationHash = shell_exec('git log -1 --format=format:"%H" -- ' . $translatedFilePath);
        return shell_exec('git diff ' . $lastTranslationHash . '..HEAD -- ' . $sourceFilePath);
    }

    /**
     * Adds all necessary HTML tags and classes to diff output.
     *
     * @param string $diff DIFF
     * @return string highlighted DIFF
     */
    public function highlightDiff($diff)
    {
        $lines = explode("\n", $diff);
        foreach ($lines as $key => $val) {
            if (strpos($val, '@') === 0) {
                $lines[$key] = '<span class="info">' . Html::encode($val) . '</span>';
            } elseif (strpos($val, '+') === 0) {
                $lines[$key] = '<ins>' . Html::encode($val) . '</ins>';
            } elseif (strpos($val, '-') === 0) {
                $lines[$key] = '<del>' . Html::encode($val) . '</del>';
            } else {
                $lines[$key] = Html::encode($val);
            }
        }

        return implode("\n", $lines);
    }
}
