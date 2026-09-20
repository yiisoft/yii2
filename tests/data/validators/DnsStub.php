<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\validators {
    /**
     * Stub for the global `dns_get_record()` function used by {@see \yii\validators\EmailValidator}.
     *
     * Produces only the two lookup outcomes real DNS cannot deliver (a `false` result and a thrown `ErrorException`),
     * and delegates every other lookup to the global function.
     *
     * @author Wilmer Arambula <terabytesoftw@gmail.com>
     * @since 2.0.56
     */
    final class DnsStub
    {
        /**
         * @var array `dns_get_record()` answers indexed by hostname and `DNS_*` type.
         */
        public static $records = [];
        /**
         * @var array exceptions thrown by `dns_get_record()` indexed by hostname and `DNS_*` type.
         */
        public static $exceptions = [];
        /**
         * @var array recorded lookups as `[hostname, type]`.
         */
        public static $calls = [];

        /**
         * Discards the configured answers and the recorded lookups.
         */
        public static function reset(): void
        {
            self::$records = [];
            self::$exceptions = [];
            self::$calls = [];
        }
    }
}

namespace yii\validators {
    use yiiunit\data\validators\DnsStub;

    use function array_key_exists;
    use function func_num_args;
    use function function_exists;

    if (!function_exists('yii\validators\dns_get_record')) {
        /**
         * Answers the record lookup with the value configured in {@see DnsStub}.
         *
         * @param string $hostname hostname to look up, as built by the validator (with the trailing dot).
         * @param int $type `DNS_*` bitmask of the records to retrieve.
         * @param array|null $authoritativeNameServers authoritative name servers, filled by the global function.
         * @param array|null $additionalRecords additional records, filled by the global function.
         * @param bool $raw whether to return the raw record data.
         * @return array|false the matching records, or `false` when the lookup fails.
         */
        function dns_get_record(
            $hostname,
            $type = DNS_ANY,
            &$authoritativeNameServers = null,
            &$additionalRecords = null,
            $raw = false
        ) {
            DnsStub::$calls[] = [$hostname, $type];

            if (isset(DnsStub::$exceptions[$hostname][$type])) {
                throw DnsStub::$exceptions[$hostname][$type];
            }

            if (
                array_key_exists($hostname, DnsStub::$records)
                && array_key_exists($type, DnsStub::$records[$hostname])
            ) {
                return DnsStub::$records[$hostname][$type];
            }

            if (func_num_args() > 2) {
                return \dns_get_record($hostname, $type, $authoritativeNameServers, $additionalRecords, $raw);
            }

            // holders for the authority and additional sections trigger an extra query that many resolvers refuse
            return \dns_get_record($hostname, $type);
        }
    }
}
