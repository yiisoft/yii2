<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\profile;

use Yii;
use yii\base\Component;

/**
 * Profiler provides profiling support.
 *
 * @author Paul Klimov <klimov-paul@gmail.com>
 * @since 2.1
 */
class Profiler extends Component implements ProfilerInterface
{
    /**
     * Profiling message level. This indicates the message is for profiling purpose.
     */
    const LEVEL_PROFILE = 0x40;
    /**
     * Profiling message level. This indicates the message is for profiling purpose. It marks the
     * beginning of a profiling block.
     */
    const LEVEL_PROFILE_BEGIN = 0x50;
    /**
     * Profiling message level. This indicates the message is for profiling purpose. It marks the
     * end of a profiling block.
     */
    const LEVEL_PROFILE_END = 0x60;


    /**
     * {@inheritdoc}
     */
    public function begin($token, $category)
    {
        Yii::getLogger()->log(self::LEVEL_PROFILE_BEGIN, $token, ['category' => $category]);
    }

    /**
     * {@inheritdoc}
     */
    public function end($token, $category)
    {
        Yii::getLogger()->log(self::LEVEL_PROFILE_END, $token, ['category' => $category]);
    }
}