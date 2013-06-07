<?php

class Yii2_Sniffs_Language_ElseifSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_ELSE,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
		$tokens = $file->getTokens();
		if ($tokens[$pointer]['content'] === 'else' &&
			$tokens[$pointer + 1]['type'] === 'T_WHITESPACE' &&
			$tokens[$pointer + 2]['type'] === 'T_IF') {
			$file->addError('`else if` is not allowed. Use `elseif` instead.', $pointer);
		}
	}
}
