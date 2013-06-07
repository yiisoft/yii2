<?php

class Yii2_Sniffs_Classes_StudlyCapsSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_CLASS,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
		$tokens = $file->getTokens();
		if ($tokens[$pointer]['content'] === 'class') {
			$className = $tokens[$pointer + 2]['content'];
			if (preg_match('/^[A-Za-z0-9]+$/', $className) === 0 ||
				preg_match('/^[A-Z]{1}/', $className) === 0 ||
				preg_match('/[A-Z]/', $className) === 0 ||
				preg_match('/[a-z]/', $className) === 0) {
				$file->addError('Class name must have StudlyCaps style.', $pointer);
			}
		}
	}
}
