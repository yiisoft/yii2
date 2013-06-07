<?php

class Yii2_Sniffs_Constants_UpperCaseSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_CONST,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
		$tokens = $file->getTokens();
		if ($tokens[$pointer]['content'] === 'const') {
			$constantName = $tokens[$pointer + 2]['content'];
			if (preg_match('/^[A-Z0-9_]+$/', $constantName) === 0) {
				$file->addError('Constant name must have UPPER_CASED_SNAKE_CASE style.', $pointer);
			}
		}
	}
}
