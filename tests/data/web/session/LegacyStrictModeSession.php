<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\web\session;

use yii\web\CacheSession;

/**
 * Stub for {@see \yii\web\CacheSession} that follows the deprecated strict mode contract of Yii 2.0.38, as custom
 * storage extensions such as `yii\redis\Session` do: {@see openSession()} flags an unknown ID for regeneration and
 * {@see writeSession()} skips the flagged ID.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.56
 */
final class LegacyStrictModeSession extends CacheSession
{
    /**
     * @var bool whether {@see sessionIdExists()} checks the cache, as an extension updated for Yii 2.0.56 does, or
     * accepts every ID, as an extension written for an earlier version does.
     */
    public $nativeIdValidation = false;

    public function openSession($savePath, $sessionName)
    {
        if ($this->getUseStrictMode()) {
            $id = $this->getId();

            if (!$this->cache->exists($this->calculateKey($id))) {
                $this->_forceRegenerateId = $id;
            }
        }

        return parent::openSession($savePath, $sessionName);
    }

    public function sessionIdExists($id)
    {
        return $this->nativeIdValidation ? parent::sessionIdExists($id) : true;
    }

    public function writeSession($id, $data)
    {
        if ($this->getUseStrictMode() && $id === $this->_forceRegenerateId) {
            return true;
        }

        return parent::writeSession($id, $data);
    }
}
