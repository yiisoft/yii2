<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\console;

use ReflectionClass;
use yii\console\ExitCode;
use yiiunit\TestCase;

/**
 * @group console
 */
class ExitCodeTest extends TestCase
{
    /**
     * Values must match FreeBSD sysexits(3) spec.
     * Unlike other constant tests, these catch drift from an external standard
     * that current code-level tests cannot detect.
     *
     * @see https://man.openbsd.org/sysexits
     */
    public function testConstantsMatchSysexitsSpec()
    {
        $this->assertSame(0, ExitCode::OK);
        $this->assertSame(1, ExitCode::UNSPECIFIED_ERROR);
        $this->assertSame(64, ExitCode::USAGE);
        $this->assertSame(65, ExitCode::DATAERR);
        $this->assertSame(66, ExitCode::NOINPUT);
        $this->assertSame(67, ExitCode::NOUSER);
        $this->assertSame(68, ExitCode::NOHOST);
        $this->assertSame(69, ExitCode::UNAVAILABLE);
        $this->assertSame(70, ExitCode::SOFTWARE);
        $this->assertSame(71, ExitCode::OSERR);
        $this->assertSame(72, ExitCode::OSFILE);
        $this->assertSame(73, ExitCode::CANTCREAT);
        $this->assertSame(74, ExitCode::IOERR);
        $this->assertSame(75, ExitCode::TEMPFAIL);
        $this->assertSame(76, ExitCode::PROTOCOL);
        $this->assertSame(77, ExitCode::NOPERM);
        $this->assertSame(78, ExitCode::CONFIG);
    }

    public function testReasonsArrayCoversAllConstants()
    {
        $reflection = new ReflectionClass(ExitCode::class);
        $constants = $reflection->getConstants();

        foreach ($constants as $name => $value) {
            $this->assertArrayHasKey(
                $value,
                ExitCode::$reasons,
                "Constant $name ($value) has no entry in \$reasons"
            );
        }
    }

    public function testReasonsArrayHasNoOrphanEntries()
    {
        $reflection = new ReflectionClass(ExitCode::class);
        $constantValues = array_values($reflection->getConstants());

        foreach (array_keys(ExitCode::$reasons) as $code) {
            $this->assertContains(
                $code,
                $constantValues,
                "Reason code $code has no matching constant"
            );
        }
    }

    public function testReasonTextsAreNonEmptyStrings()
    {
        foreach (ExitCode::$reasons as $code => $reason) {
            $this->assertIsString($reason, "Reason for code $code must be a string");
            $this->assertNotEmpty($reason, "Reason for code $code must not be empty");
        }
    }

    /**
     * @dataProvider getReasonProvider
     */
    public function testGetReason($exitCode, $expectedReason)
    {
        $this->assertSame($expectedReason, ExitCode::getReason($exitCode));
    }

    public function getReasonProvider()
    {
        return [
            'OK' => [ExitCode::OK, 'Success'],
            'UNSPECIFIED_ERROR' => [ExitCode::UNSPECIFIED_ERROR, 'Unspecified error'],
            'USAGE' => [ExitCode::USAGE, 'Incorrect usage, argument or option error'],
            'DATAERR' => [ExitCode::DATAERR, 'Error in input data'],
            'NOINPUT' => [ExitCode::NOINPUT, 'Input file not found or unreadable'],
            'NOUSER' => [ExitCode::NOUSER, 'User not found'],
            'NOHOST' => [ExitCode::NOHOST, 'Host not found'],
            'UNAVAILABLE' => [ExitCode::UNAVAILABLE, 'A required service is unavailable'],
            'SOFTWARE' => [ExitCode::SOFTWARE, 'Internal error'],
            'OSERR' => [ExitCode::OSERR, 'Error making system call or using OS service'],
            'OSFILE' => [ExitCode::OSFILE, 'Error accessing system file'],
            'CANTCREAT' => [ExitCode::CANTCREAT, 'Cannot create output file'],
            'IOERR' => [ExitCode::IOERR, 'I/O error'],
            'TEMPFAIL' => [ExitCode::TEMPFAIL, 'Temporary failure'],
            'PROTOCOL' => [ExitCode::PROTOCOL, 'Unexpected remote service behavior'],
            'NOPERM' => [ExitCode::NOPERM, 'Insufficient permissions'],
            'CONFIG' => [ExitCode::CONFIG, 'Configuration error'],
        ];
    }

    public function testGetReasonReturnsUnknownForUndefinedCode()
    {
        $this->assertSame('Unknown exit code', ExitCode::getReason(42));
    }

    public function testGetReasonReturnsUnknownForNegativeCode()
    {
        $this->assertSame('Unknown exit code', ExitCode::getReason(-1));
    }

    public function testGetReasonUsesLateStaticBinding()
    {
        $subclass = new class extends ExitCode {
            public static $reasons = [
                self::OK => 'Custom success',
            ];
        };

        $this->assertSame('Custom success', $subclass::getReason(0));
        $this->assertSame('Unknown exit code', $subclass::getReason(1));
    }
}
