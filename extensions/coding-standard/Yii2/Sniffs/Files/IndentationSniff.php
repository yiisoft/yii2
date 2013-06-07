<?php

class Yii2_Sniffs_Files_IndentationSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_WHITESPACE,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
        $tokens = $file->getTokens();
        $line = $tokens[$pointer]['line'];
        if ($pointer > 0 && $tokens[$pointer - 1]['line'] === $line) {
            return;
        }
        if (strpos($tokens[$pointer]['content'], ' ') !== false) {
            $file->addError('Code must use tabs for indentation, spaces currently used.', $pointer);
        }
	}
}
