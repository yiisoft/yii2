<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\helpers\providers;

use stdClass;

/**
 * Data provider for {@see \yiiunit\framework\helpers\VarDumperTest} test cases.
 */
final class VarDumperProvider
{
    /**
     * Provides values with their expected highlighted dump before and since PHP 8.3.
     *
     * Since PHP 8.3 `highlight_string()` wraps the output in `<pre><code>` and keeps whitespace as is instead of
     * converting it to `<br />` and `&nbsp;`, which is why the open tag `<?php` has to be stripped differently.
     *
     * @phpstan-return array<string, array{mixed, string, string}>
     */
    public static function dumpAsStringHighlighted(): array
    {
        $object = new stdClass();
        $object->a = 1;

        return [
            'null' => [
                null,
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB">null</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB">null</span></code></pre>
                HTML,
            ],
            'bool' => [
                true,
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB">true</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB">true</span></code></pre>
                HTML,
            ],
            'int' => [
                1,
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB">1</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB">1</span></code></pre>
                HTML,
            ],
            'float' => [
                1.5,
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB">1.5</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB">1.5</span></code></pre>
                HTML,
            ],
            'string' => [
                'string',
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB"></span><span style="color: #DD0000">'string'</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB"></span><span style="color: #DD0000">'string'</span></code></pre>
                HTML,
            ],
            'string with php open tag' => [
                '<?php',
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB"></span><span style="color: #DD0000">'&lt;?php'</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB"></span><span style="color: #DD0000">'&lt;?php'</span></code></pre>
                HTML,
            ],
            'empty array' => [
                [],
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB"></span><span style="color: #007700">[]</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB"></span><span style="color: #007700">[]</span></code></pre>
                HTML,
            ],
            'nested array' => [
                ['a' => 1, 'b' => ['c' => 'd']],
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB"></span><span style="color: #007700">[<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'a'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">1<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'b'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;[<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'c'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'d'<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">]<br />]</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB"></span><span style="color: #007700">[
                    </span><span style="color: #DD0000">'a' </span><span style="color: #007700">=&gt; </span><span style="color: #0000BB">1
                    </span><span style="color: #DD0000">'b' </span><span style="color: #007700">=&gt; [
                        </span><span style="color: #DD0000">'c' </span><span style="color: #007700">=&gt; </span><span style="color: #DD0000">'d'
                    </span><span style="color: #007700">]
                ]</span></code></pre>
                HTML,
            ],
            'object' => [
                $object,
                <<<HTML
                <code><span style="color: #000000">
                <span style="color: #0000BB">stdClass</span><span style="color: #FF8000">#1<br /></span><span style="color: #007700">(<br />&nbsp;&nbsp;&nbsp;&nbsp;[</span><span style="color: #0000BB">a</span><span style="color: #007700">]&nbsp;=&gt;&nbsp;</span><span style="color: #0000BB">1<br /></span><span style="color: #007700">)</span>
                </span>
                </code>
                HTML,
                <<<HTML
                <pre><code style="color: #000000"><span style="color: #0000BB">stdClass</span><span style="color: #FF8000">#1
                </span><span style="color: #007700">(
                    [</span><span style="color: #0000BB">a</span><span style="color: #007700">] =&gt; </span><span style="color: #0000BB">1
                </span><span style="color: #007700">)</span></code></pre>
                HTML,
            ],
        ];
    }
}
