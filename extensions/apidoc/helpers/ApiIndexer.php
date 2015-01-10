<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use cebe\jssearch\Indexer;
use cebe\jssearch\tokenizer\StandardTokenizer;
use cebe\jssearch\TokenizerInterface;

/**
 * ApiIndexer indexes framework API
 */
class ApiIndexer extends Indexer
{
    /**
     * @param string $file
     * @param string $contents
     * @param string $basePath
     * @param string $baseUrl
     * @return array
     */
    protected function generateFileInfo($file, $contents, $basePath, $baseUrl)
    {
		// create file entry
        if (preg_match('~<h1>(.*|\r*|\n*?)</h1>~u', $contents, $matches)) {
            $title = str_replace('&para;', '', strip_tags($matches[1]));
        } elseif (preg_match('~<title>(.*|\r*|\n*?)</title>~u', $contents, $matches)) {
            $title = strip_tags($matches[1]);
        } else {
            $title = '<i>No title</i>';
        }

        if (preg_match('~<div id="classDescription">\s*<strong>(.*|\r*|\n*?)</strong>~u', $contents, $matches)) {
            $description = strip_tags($matches[1]);
        } elseif (preg_match('~<p>(.*|\r*|\n*?)</p>~u', $contents, $matches)) {
            $description = strip_tags($matches[1]);
            if (mb_strlen($description) > 1000) { // TODO truncate by words
                $description = mb_substr($description, 0, 1000) . '...';
            }
        } else {
            $description = '';
        }

        return [
            'u' => $baseUrl . str_replace('\\', '/', substr($file, strlen(rtrim($basePath, '\\/')))),
            't' => $title,
            'd' => $description,
        ];
    }

    /**
     * @return TokenizerInterface
     */
    public function getTokenizer()
    {
        $tokenizer = parent::getTokenizer();
        if ($tokenizer instanceof StandardTokenizer) {
            // yii is part of every doc and makes weird search results
            $tokenizer->stopWords[] = 'yii';
            $tokenizer->stopWords = array_unique($tokenizer->stopWords);
        }
        return $tokenizer;
    }
}