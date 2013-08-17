<?php
/**
 * "Context" diff renderer.
 *
 * This class renders the diff in classic "context diff" format.
 *
 * Copyright 2004-2012 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @package Text_Diff
 */
class Horde_Text_Diff_Renderer_Context extends Horde_Text_Diff_Renderer
{
    /**
     * Number of leading context "lines" to preserve.
     */
    protected $_leading_context_lines = 4;

    /**
     * Number of trailing context "lines" to preserve.
     */
    protected $_trailing_context_lines = 4;

    protected $_second_block = '';

    protected function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        if ($xlen != 1) {
            $xbeg .= ',' . $xlen;
        }
        if ($ylen != 1) {
            $ybeg .= ',' . $ylen;
        }
        $this->_second_block = "--- $ybeg ----\n";
        return "***************\n*** $xbeg ****";
    }

    protected function _endBlock()
    {
        return $this->_second_block;
    }

    protected function _context($lines)
    {
        $this->_second_block .= $this->_lines($lines, '  ');
        return $this->_lines($lines, '  ');
    }

    protected function _added($lines)
    {
        $this->_second_block .= $this->_lines($lines, '+ ');
        return '';
    }

    protected function _deleted($lines)
    {
        return $this->_lines($lines, '- ');
    }

    protected function _changed($orig, $final)
    {
        $this->_second_block .= $this->_lines($final, '! ');
        return $this->_lines($orig, '! ');
    }

}
