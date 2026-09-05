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
     * @var string the regexp to to filter test/dummy links like as `https://www.example.com`
     * @todo remove `yiiframework` after site fix
     */
    public $dummyLinkRegexp = '/^https?:\/\/(\w+\.)(myserver|example|site|test|demo|oauth|yiiframework)\./';
    /**
     * @var int[] the 3xx HTTP codes to ignore (add to active links)
     */
    public $skipHttpCodes = [300, 302, 307];

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
            if (\preg_match($this->dummyLinkRegexp, $url) === 1) {
                $this->activeLinks[] = $url;
            } else {
                $this->checkUrl($url);
            }
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
     * Returns HTTP code of last response.
     *
     * @param string[] $headers the response headers
     * @return int
     */
    protected function getResponceCode(array $headers)
    {
        list(, $responceCode) = \sscanf($headers[0], 'HTTP/1.%d %d');

        return (int) $responceCode
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
            return;
        }
        $code = $this->getResponceCode($headers);
        if (isset($headers['Location'])) {
            // at redirect
            if (\in_array($code, $this->skipHttpCodes, true)) {
                // ignore temporaty redirects
                $this->activeLinks[] = $url;
            } else {
                if (\is_array($headers['Location'])) {
                    $headers['Location'] = \reset($headers['Location']);
                }
                $newUrl = \rtrim($headers['Location'], '/');
                if (!\filter_var($newUrl, \FILTER_VALIDATE_URL)) {
                    $parts = \parse_url($url);
                    $newUrl = \ltrim($newUrl, '/');
                    $newUrl = (isset($parts['scheme']) ? $parts['scheme'] . ':' : '') . "//$parts['host']/$newUrl";
                }
                $index = \array_search($newUrl, $this->activeLinks, true);
                if ($index !== false) {
                    $this->outdatedLinks[$url] =& $this->activeLinks[$index];
                } else {
                    $this->activeLinks[] = $newUrl;
                    $this->outdatedLinks[$url] =& $this->activeLinks[\count($this->activeLinks) - 1];
                }
            }
        } elseif ($code === 200) {
            $this->activeLinks[] = $url;
        } else {
            $this->outdatedLinks[$url] = false;
        }
    }
}
