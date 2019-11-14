<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Response 表示 [[Application]] 对 [[Request]] 的响应。
 *
 * 有关 Response 的更多详细信息和用法信息，请参阅 [响应指南文章](guide:runtime-responses)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Response extends Component
{
    /**
     * @var int 退出状态码。 退出状态码应在 0 到 254 范围内。
     * 状态 0 表示程序成功终止。
     */
    public $exitStatus = 0;


    /**
     * 将响应发送给客户端。
     */
    public function send()
    {
    }

    /**
     * 清空所有现有输出缓冲区。
     */
    public function clearOutputBuffers()
    {
        // the following manual level counting is to deal with zlib.output_compression set to On
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }
}
