<?php

namespace yii\db\pgsql;

/**
 * TODO: phpdoc, tests
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class ArrayParser
{
    /**
     * @var string Character used in array
     */
    protected $delimiter = ',';

    /**
     * Convert array from PostgreSQL to PHP
     *
     * @param string $value string to be converted
     * @return array|null
     */
    public function parse($value)
    {
        if (is_null($value)) {
            return null;
        }

        if ($value === '{}') {
            return [];
        }

        return $this->parseArray($value);
    }

    private function parseArray($value, &$i = 0)
    {
        $result = [];
        for(++$i; $i < strlen($value); ++$i) {
            switch ($value[$i]) {
                case '{':
                    $result[] = $this->parseArray($value, $i);
                    break;
                case '}':
                    break 2;
                case $this->delimiter:
                    if (empty($result)) { // `{}` case
                        $result[] = null;
                    }
                    if (in_array($value[$i + 1], [$this->delimiter, '}'])) { // `{,}` case
                        $result[] = null;
                    }
                    break;
                default:
                    $result[] = $this->parseString($value, $i);
            }
        }

        return $result;
    }

    private function parseString($value, &$i)
    {
        $isQuoted = $value[$i] === '"';
        $stringEndChars = $isQuoted ? ['"'] : [$this->delimiter, '}'];
        $result = '';
        for ($i += $isQuoted ? 1 : 0; $i < strlen($value); ++$i) {
            if (in_array($value[$i], ['\\', '"'], true) && in_array($value[$i + 1], [$value[$i], '"'], true)) {
                ++$i;
            } elseif (in_array($value[$i], $stringEndChars)) {
                break;
            }

            $result .= $value[$i];
        }

        $i -= $isQuoted ? 0 : 1;

        if (!$isQuoted && $result === 'NULL') {
            $result = null;
        }

        return $result;
    }
}
