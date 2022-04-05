<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\helpers;

/**
 * Checks the active/actual URLs.
 *
 * @author Anton Fedonyuk <info@ensostudio.ru>
 * @since 2.0.46
 */
class LinkChecker
{
    /**
     * @var array the active links
     */
    protected $activeLinks = [];
    /**
     * @var array the outdated links
     */
    protected $outdatedLinks = [];

    /**
     * Checks if link is outdated and returns active/actual link.
     *
     * @param string $url the link to check
     * @return string|false the active link or FALSE(link removed)
     */
    public function check($url)
    {
        $url = \rtrim($url, '/');
        if (!$this->isCheckedUrl($url)) {
            $this->checkUrl($url);
        }

        return isset($this->outdatedLinks[$url]) ? $this->outdatedLinks[$url] : $url;
    }

    /**
     * Checks if link is already checked.
     *
     * @param string $url the link to test
     * @return bool
     */
    protected function isCheckedUrl($url)
    {
        return isset($this->outdatedLinks[$url]) || \in_array($url, $this->activeLinks, true);
    }

    /**
     * Checks link and stores result.
     *
     * @param string $url the link to test
     * @return void
     */
    protected function checkUrl($url)
    {
        $headers = @\get_headers($url, true);
        if ($headers === false) {
            $this->outdatedLinks[$url] = false;
        } elseif (isset($headers['Location'])) {
            // at redirect to new URL:
            if (\is_array($headers['Location'])) {
                $headers['Location'] = \reset($headers['Location']);
            }
            $newUrl = \rtrim($headers['Location'], '/');
            $index = \array_search($newUrl, $this->activeLinks, true);
            if ($index !== false) {
                $this->outdatedLinks[$url] =& $this->activeLinks[$index];
            } else {
                $this->activeLinks[] = $newUrl;
                $this->outdatedLinks[$url] =& $this->activeLinks[\count($this->activeLinks) - 1];
            }
            
        } else {
            list(, $responceCode) = \sscanf($headers[0], 'HTTP/1.%d %d');
            if ($responceCode === 200) {
                $this->activeLinks[] = $url;
            } else {
                $this->outdatedLinks[$url] = false;
            }
        }
    }
}
