<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\cs;

use PhpCsFixer\Config;
use yii\helpers\ArrayHelper;

/**
 * Basic rules used by Yii 2 ecosystem.
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 * @since 2.0.0
 */
class YiiConfig extends Config
{
    /**
     * {@inheritdoc}
     */
    public function __construct($name = 'yii-cs-config')
    {
        parent::__construct($name);

        $this->setRiskyAllowed(true);

        $this->setRules([
            '@PSR2' => true,
            'array_syntax' => [
                'syntax' => 'short',
            ],
            'binary_operator_spaces' => [
                'align_double_arrow' => false,
                'align_equals' => false,
            ],
            'cast_spaces' => true,
            'concat_space' => [
                'spacing' => 'one',
            ],
            'dir_constant' => true,
            'ereg_to_preg' => true,
            'function_typehint_space' => true,
            'hash_to_slash_comment' => true,
//            'heredoc_to_nowdoc' => true,
//            'include' => true,
            'is_null' => [
                'use_yoda_style' => false,
            ],
            'linebreak_after_opening_tag' => true,
            'lowercase_cast' => true,
            'magic_constant_casing' => true,
//            'mb_str_functions' => true,
//            'method_separation' => true, // conflicts with current Yii style with double line between properties and methods
            'modernize_types_casting' => true,
            'native_function_casing' => true,
            'new_with_braces' => true,
            'no_alias_functions' => true,
            'no_blank_lines_after_class_opening' => true,
            'no_blank_lines_after_phpdoc' => true,
            'no_empty_comment' => true,
            'no_empty_phpdoc' => true,
            'no_empty_statement' => true,
            'no_extra_consecutive_blank_lines' => [
                'tokens' => [
                    'break',
                    'continue',
//                    'extra', // conflicts with current Yii style with double line between properties and methods
                    'return',
                    'throw',
                    'use',
                    'use_trait',
//                    'curly_brace_block', // breaks namespaces blocks
                    'parenthesis_brace_block',
                    'square_brace_block',
                ],
            ],
            'no_leading_import_slash' => true,
            'no_leading_namespace_whitespace' => true,
            'no_mixed_echo_print' => true,
            'no_multiline_whitespace_around_double_arrow' => true,
            'no_multiline_whitespace_before_semicolons' => true,
            'no_php4_constructor' => true,
            'no_short_bool_cast' => true,
            'no_singleline_whitespace_before_semicolons' => true,
            'no_spaces_around_offset' => true,
            'no_trailing_comma_in_list_call' => true,
            'no_trailing_comma_in_singleline_array' => true,
            'no_unneeded_control_parentheses' => true,
            'no_unused_imports' => true,
//            'no_useless_else' => true, // needs more discussion
            'no_useless_return' => true,
            'no_whitespace_before_comma_in_array' => true,
            'no_whitespace_in_blank_line' => true,
//            'non_printable_character' => true, // breaks Formatter::asCurrency() tests
            'normalize_index_brace' => true,
        ]);
    }

    /**
     * Merge current rules config with provided list of rules.
     *
     * @param array $rules
     * @return $this
     * @see setRules()
     * @see ArrayHelper::merge()
     */
    public function mergeRules(array $rules)
    {
        parent::setRules(ArrayHelper::merge($this->getRules(), $rules));

        return $this;
    }
}
