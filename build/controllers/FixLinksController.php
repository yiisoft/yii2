<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use yii\build\helpers\LinkChecker;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use Yii;

/**
 * Replaces outdated links (3xx response codes) add display removed links (4xx response codes).
 *
 * This method scan PHP sources (DocBlock) and documentation (Markdown).
 *
 * @author Anton Fedonyuk <info@ensostudio.ru>
 * @since 2.0.46
 */
class FixLinksController extends Controller
{
    /**
     * @var LinkChecker
     */
    private $linkChecker;

    /**
     * @var bool the "safe mode" option: output outdated links instead of replacing them in files
     */
    public $safeMode = true;

    /**
     * @inheritDoc
     */
    public function options($actionID)
    {
        return \array_merge(parent::options($actionID), ['safeMode']);
    }

    /**
     * Replaces outdated links add log removed links.
     *
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $this->linkChecker = new LinkChecker();
        $this->fixSources();
        $this->fixDocs();

        return ExitCode::OK;
    }

    /**
     * Replaces outdated links add log removed links in PHP sources (DocBlock).
     *
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSources()
    {
        $this->linkChecker = new LinkChecker();
        $this->fixSources();

        return ExitCode::OK;
    }
    /**
     * Replaces outdated links add log removed links in Markdown documentation.
     *
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDocs()
    {
        $this->linkChecker = new LinkChecker();
        $this->fixDocs();

        return ExitCode::OK;
    }

    /**
     * Fix outdated links in PHP sources(DocBlock).
     *
     * @return void
     */
    private function fixSources()
    {
        $files = FileHelper::findFiles(
            \dirname(__DIR__, 2) . '/framework',
            [
                'only' => ['*.php'],
                'except' => [
                    '/messages/',
                    '/requirements/',
                    '/views/',
                    '/assets/',
                    '/*/views/',
                    '/*/migrations/'
                ],
            ]
        );
        foreach ($files as $file) {
            $content = \file_get_contents($file);
            $tokens = \token_get_all($content);
            $outdated = ['updated' => [], 'removed' => []];
            foreach ($tokens as $token) {
                // check links only in comments
                if (!\is_array($token) || !\in_array($token[0], [\T_COMMENT, \T_DOC_COMMENT], true)) {
                    continue;
                }
                $outdatedPart = $this->findOutdatedLinks($token[1]);
                $outdated['updated'][] = $outdatedPart['updated'];
                $outdated['removed'][] = $outdatedPart['removed'];
            }
            $this->fixAndReportOutdatedLinks($file, $content, $outdated['updated'], $outdated['removed']);
        }
    }

    /**
     * Fix outdated links in documentation(Markdown).
     *
     * @return void
     */
    private function fixDocs()
    {
        $files = FileHelper::findFiles(
            \dirname(__DIR__, 2) . '/docs',
            [
                'only' => ['*.md'],
                'except' => ['/*/images/'],
            ]
        );
        foreach ($files as $file) {
            $content = \file_get_contents($file);
            // ignore code examples
            $parts = \preg_split('/(```.+?```|`.+?`|~~~.+?~~~)/s', $content, -1, \PREG_SPLIT_NO_EMPTY);
            $outdated = ['updated' => [], 'removed' => []];
            foreach ($parts as $value) {
                $outdatedPart = $this->findOutdatedLinks($value);
                $outdated['updated'][] = $outdatedPart['updated'];
                $outdated['removed'][] = $outdatedPart['removed'];
            }
            $this->fixAndReportOutdatedLinks($file, $content, $outdated['updated'], $outdated['removed']);
        }
    }

    /**
     * @var string $file the file to fix
     * @var string $content the file content
     * @var array $updated the updated URLs
     * @var array $removed the removed URLs
     * @return void
     */
    private function fixAndReportOutdatedLinks($file, $content, array $updated, array $removed)
    {
        $updated = \array_filter($updated);
        if (!empty($updated)) {
            $updated = \call_user_func_array('array_merge', $updated);
            if ($this->safeMode) {
                $this->stderr("Updated links in file '$file':\n", Console::FG_YELLOW);
                foreach ($updated as $oldLink => $newLink) {
                    $this->stderr("$oldLink > $newLink\n");
                }
            } else {
                // Safe mode: off
                \file_put_contents($file, \strtr($content, $updated));
            }
        }

        $removed = \array_filter($removed);
        if (!empty($removed)) {
            $this->stderr("Removed links in file '$file':\n", Console::FG_RED);
            $removed = \call_user_func_array('array_merge', $removed);
            $removed = \implode("\n", \array_unique($removed));
            $this->stderr("$removed\n");
        }
    }

    /**
     * Finds outdated links.
     *
     * @param string $str the string to scan
     * @return array[] `['updated' => [old URL => new URL], 'removed' => URLs]`
     */
    private function findOutdatedLinks($str)
    {
        if (!\preg_match_all(
            '%https?://(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+'
            . '(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*'
            . '\.[a-z\x{00a1}-\x{ffff}]{2,6}(?:[^\s}{)("]+)?%iu',
            $str,
            $urls
        )) {
            return ['updated' => [], 'removed' => []];
        }

        $outdated = ['updated' => [], 'removed' => []];
        foreach (\array_unique($urls[0]) as $url) {
            $url = \rtrim($url, ' ],;\'"`>');
            $activeUrl = $this->linkChecker->check($url);
            if ($activeUrl === false) {
                $outdated['removed'][] = $url;
            } elseif ($activeUrl !== $url) {
                $outdated['updated'][$url] = $activeUrl;
            }
        }

        return $outdated;
    }
}
