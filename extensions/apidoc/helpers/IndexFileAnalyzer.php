<?php
/**
 * Created by PhpStorm.
 * User: cebe
 * Date: 26.05.14
 * Time: 18:15
 */

namespace yii\apidoc\helpers;


use cebe\markdown\Markdown;

class IndexFileAnalyzer extends Markdown
{
    private $_chapter = 0;
    private $_chapters = [];

    public function analyze($text)
    {
        $this->parse($text);

        return $this->_chapters;
    }

    protected function renderHeadline($block)
    {
        $this->_chapters[++$this->_chapter] = [
            'headline' => $block['content'],
            'content' => [],
        ];
        return parent::renderHeadline($block);
    }

    protected function renderList($block)
    {
        foreach ($block['items'] as $item => $itemLines) {
            if (preg_match('~\[([^\]]+)\]\(([^\)]+)\)(.*)~', implode("\n", $itemLines), $matches)) {
                $this->_chapters[$this->_chapter]['content'][] = [
                    'headline' => $matches[1],
                    'file' => $matches[2],
                    'teaser' => $matches[3],
                ];
            }
        }
        return parent::renderList($block);
    }
} 