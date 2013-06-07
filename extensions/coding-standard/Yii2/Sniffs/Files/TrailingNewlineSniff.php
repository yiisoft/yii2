<?php

class Yii2_Sniffs_Files_TrailingNewlineSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_WHITESPACE,
			T_CLOSE_CURLY_BRACKET,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
		$tokens = $file->getTokens();
		if ($pointer == $file->numTokens - 1 && $tokens[$pointer]['content'] !== "\n") {
			$file->addError('Newline at the of file is mandatory.', $pointer);
		}
	}
}
