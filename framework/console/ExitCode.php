<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

/**
 * 此类提供用于定义控制台命令退出代码的常量。
 *
 * 退出代码遵循 [FreeBSD sysexits(3)](http://man.openbsd.org/sysexits) 手册页中定义的代码。
 *
 * 这些常量可以在控制台控制器中使用，例如：
 *
 * ```php
 * public function actionIndex()
 * {
 *     if (!$this->isAllowedToPerformAction()) {
 *          $this->stderr('Error: ' . ExitCode::getReason(ExitCode::NOPERM));
 *          return ExitCode::NOPERM;
 *     }
 *
 *     // do something
 *
 *     return ExitCode::OK;
 * }
 * ```
 *
 * @author Tom Worster <fsb@thefsb.org>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @see http://man.openbsd.org/sysexits
 * @since 2.0.13
 */
class ExitCode
{
    /**
     * 命令成功完成。
     */
    const OK = 0;
    /**
     * 命令退出时带有一个错误代码，该代码对错误没有任何说明。
     */
    const UNSPECIFIED_ERROR = 1;
    /**
     * 命令使用不正确，例如，错误的参数数量，
     * 坏标志，参数中的错误语法或其他。
     */
    const USAGE = 64;
    /**
     * 输入数据在某种程度上是不正确的。这应该只用于
     * 用户的数据，而不是系统文件。
     */
    const DATAERR = 65;
    /**
     * 输入文件（不是系统文件）不存在或不可读。
     * 这也可能包括邮件中的 ``No message''
     * 等错误（如果它想捕获它）。
     */
    const NOINPUT = 66;
    /**
     * 指定的用户不存在。这可以用于邮件地址
     * 或远程登陆。
     */
    const NOUSER = 67;
    /**
     * 指定的主机不存在。这被用于邮件地址或
     * 网络请求。
     */
    const NOHOST = 68;
    /**
     * 服务不可用。如果支持程序或文件不存在，
     * 可能会发生这种情况。这也可以用作一条覆盖所有的消息，
     * 当你想要做的事情不起作用，但您不知道原因时。
     */
    const UNAVAILABLE = 69;
    /**
     * 检测到内部软件错误。这应尽可能限于
     * 与操作系统无关的错误。
     */
    const SOFTWARE = 70;
    /**
     * 检测到操作系统错误。这是为了
     * 用于诸如 ``cannot fork''，``cannot create pipe''，或
     * 类似的。它包括像 getuid 返回一个在 passwd 文件中不存在
     * 的用户。
     */
    const OSERR = 71;
    /**
     * 某些系统文件（例如，/etc/passwd，/var/run/utx.active，等等。）
     * 不存在，不能被打开，或者有某种错误（例如，syntax error）。
     */
    const OSFILE = 72;
    /**
     * 无法创建（用户指定的）输出文件。
     */
    const CANTCREAT = 73;
    /**
     * 在某些文件上执行 I/O 时发生错误。
     */
    const IOERR = 74;
    /**
     * 暂时失败，表明某些事情并非真正的错误。
     * 在发送邮件中，这意味着一个邮件发件人（例如）无法创建连接，
     * 请求应稍后重试。
     */
    const TEMPFAIL = 75;
    /**
     * 远程系统在协议交换期间返回了 ``not possible''
     * 的内容。
     */
    const PROTOCOL = 76;
    /**
     * 您没有足够的权限来执行操作。这
     * 不是针对应该使用 NOINPUT 或 CANTCREAT 的文件系统问题，
     * 而是针对较高级别的权限。
     */
    const NOPERM = 77;
    /**
     * 在未配置或错误配置状态下发现了某些内容。
     */
    const CONFIG = 78;

    /**
     * @var array 退出代码的原因说明的映射。
     */
    public static $reasons = [
        self::OK => 'Success',
        self::UNSPECIFIED_ERROR => 'Unspecified error',
        self::USAGE => 'Incorrect usage, argument or option error',
        self::DATAERR => 'Error in input data',
        self::NOINPUT => 'Input file not found or unreadable',
        self::NOUSER => 'User not found',
        self::NOHOST => 'Host not found',
        self::UNAVAILABLE => 'A requied service is unavailable',
        self::SOFTWARE => 'Internal error',
        self::OSERR => 'Error making system call or using OS service',
        self::OSFILE => 'Error accessing system file',
        self::CANTCREAT => 'Cannot create output file',
        self::IOERR => 'I/O error',
        self::TEMPFAIL => 'Temporary failure',
        self::PROTOCOL => 'Unexpected remote service behavior',
        self::NOPERM => 'Insufficient permissions',
        self::CONFIG => 'Configuration error',
    ];


    /**
     * 返回给定退出代码的简短原因文本。
     *
     * 此方法使用 [[$reasons]] 来确定退出代码的原因。
     * @param int $exitCode 此类中定义的常量之一。
     * @return string 原因文本，或 `"Unknown exit code"` 如果代码未在 [[$reasons]] 中列出。
     */
    public static function getReason($exitCode)
    {
        return isset(static::$reasons[$exitCode]) ? static::$reasons[$exitCode] : 'Unknown exit code';
    }
}
