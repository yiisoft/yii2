<?php
/**
 * Copyright 2007-2012 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @package Text_Diff
 * @author  Geoffrey T. Dairiki <dairiki@dairiki.org>
 */
class Horde_Text_Diff_ThreeWay_BlockBuilder
{
    public function __construct()
    {
        $this->_init();
    }

    public function input($lines)
    {
        if ($lines) {
            $this->_append($this->orig, $lines);
        }
    }

    public function out1($lines)
    {
        if ($lines) {
            $this->_append($this->final1, $lines);
        }
    }

    public function out2($lines)
    {
        if ($lines) {
            $this->_append($this->final2, $lines);
        }
    }

    public function isEmpty()
    {
        return !$this->orig && !$this->final1 && !$this->final2;
    }

    public function finish()
    {
        if ($this->isEmpty()) {
            return false;
        } else {
            $edit = new Horde_Text_Diff_ThreeWay_Op_Base($this->orig, $this->final1, $this->final2);
            $this->_init();
            return $edit;
        }
    }

    protected function _init()
    {
        $this->orig = $this->final1 = $this->final2 = array();
    }

    protected function _append(&$array, $lines)
    {
        array_splice($array, sizeof($array), 0, $lines);
    }
}
