<?php
/**
 * "Unified" diff renderer.
 *
 * This class renders the diff in classic "unified diff" format.
 *
 * Copyright 2004-2012 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you did
 * not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author  Ciprian Popovici
 * @package Text_Diff
 */
class Horde_Text_Diff_Renderer_Unified extends Horde_Text_Diff_Renderer
{
    /**
     * Number of leading context "lines" to preserve.
     */
    protected $_leading_context_lines = 4;

    /**
     * Number of trailing context "lines" to preserve.
     */
    protected $_trailing_context_lines = 4;

    protected function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        if ($xlen != 1) {
            $xbeg .= ',' . $xlen;
        }
        if ($ylen != 1) {
            $ybeg .= ',' . $ylen;
        }
        return "@@ -$xbeg +$ybeg @@";
    }

    protected function _context($lines)
    {
        return $this->_lines($lines, ' ');
    }

    protected function _added($lines)
    {
        return $this->_lines($lines, '+');
    }

    protected function _deleted($lines)
    {
        return $this->_lines($lines, '-');
    }

    protected function _changed($orig, $final)
    {
        return $this->_deleted($orig) . $this->_added($final);
    }
}
