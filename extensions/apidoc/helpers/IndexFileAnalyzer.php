<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use cebe\markdown\Markdown;

class IndexFileAnalyzer extends Markdown
{
    public $title;
    public $introduction;

    private $_chapter = 0;
    private $_chapters = [];


    public function analyze($text)
    {
        $this->parse($text);

        return $this->_chapters;
    }

    protected function renderHeadline($block)
    {
        if ($this->_chapter === 0) {
            $this->title = $this->renderAbsy($block['content']);
            $this->introduction = '';
            $this->_chapter++;
        } else {
            $this->_chapter++;
            $this->_chapters[$this->_chapter] = [
                'headline' => $this->renderAbsy($block['content']),
                'content' => [],
            ];
        }
        return parent::renderHeadline($block);
    }

    protected function renderParagraph($block)
    {
        if ($this->_chapter < 1) {
            $this->introduction .= $this->renderAbsy($block['content']);
        }
        return parent::renderParagraph($block);
    }

    protected function renderList($block)
    {
        if ($this->_chapter > 0) {
            foreach ($block['items'] as $item => $absyElements) {
                foreach($absyElements as $element) {
                    if ($element[0] === 'link') {
                        $this->_chapters[$this->_chapter]['content'][] = [
                            'headline' => $this->renderAbsy($element['text']),
                            'file' => $element['url'],
                        ];
                    }
                }
            }
        }
        return parent::renderList($block);
    }
}
