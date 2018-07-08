<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\profile;

/**
 * ProfilerInterface describes a profiler instance.
 *
 * A Profiler instance can be accessed via `Yii::getProfiler()`.
 *
 * For more details and usage information on Profiler, see the [guide article on profiling](guide:runtime-profiling)
 *
 * @author Paul Klimov <klimov-paul@gmail.com>
 * @since 3.0.0
 */
interface ProfilerInterface
{
    /**
     * Marks the beginning of a code block for profiling.
     * This has to be matched with a call to [[end()]] with the same category name.
     * The begin- and end- calls must also be properly nested. For example,
     *
     * ```php
     * \Yii::getProfiler()->begin('block1');
     * // some code to be profiled
     *     \Yii::getProfiler()->begin('block2');
     *     // some other code to be profiled
     *     \Yii::getProfiler()->end('block2');
     * \Yii::getProfiler()->end('block1');
     * ```
     * @param string $token token for the code block
     * @param array $context the context data of this profile block
     * @see endProfile()
     */
    public function begin($token, array $context = []);

    /**
     * Marks the end of a code block for profiling.
     * This has to be matched with a previous call to [[begin()]] with the same category name.
     * @param string $token token for the code block
     * @param array $context the context data of this profile block
     * @see begin()
     */
    public function end($token, array $context = []);

    /**
     * Flushes profiling messages from memory to actual storage.
     */
    public function flush();
}