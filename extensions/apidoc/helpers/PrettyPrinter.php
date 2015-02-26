<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use PHPParser_Node_Expr;
use PHPParser_Node_Expr_Array;

/**
 * Enhances the phpDocumentor PrettyPrinter with short array syntax
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class PrettyPrinter extends \phpDocumentor\Reflection\PrettyPrinter
{
    /**
     * @param PHPParser_Node_Expr_Array $node
     * @return string
     */
    public function pExpr_Array(PHPParser_Node_Expr_Array $node)
    {
        return '[' . $this->pCommaSeparated($node->items) . ']';
    }

    /**
     * Returns a simple human readable output for a value.
     *
     * @param PHPParser_Node_Expr $value The value node as provided by PHP-Parser.
     * @return string
     */
    public static function getRepresentationOfValue(PHPParser_Node_Expr $value)
    {
        if ($value === null) {
            return '';
        }

        $printer = new static();

        return $printer->prettyPrintExpr($value);
    }
}
