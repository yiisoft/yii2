<?php
/*
 * Ensures compatibility with PHPUnit 6.x
 */

if (!class_exists('PHPUnit_Framework_Constraint') && class_exists('PHPUnit\Framework\Constraint\Constraint')) {
    class PHPUnit_Framework_Constraint extends \PHPUnit\Framework\Constraint\Constraint {}
}
