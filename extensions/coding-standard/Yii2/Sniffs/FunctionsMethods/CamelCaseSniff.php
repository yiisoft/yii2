<?php

class Yii2_Sniffs_FunctionsMethods_CamelCaseSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_FUNCTION,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
		$tokens = $file->getTokens();
		if ($tokens[$pointer]['content'] === 'function') {
			$name = $tokens[$pointer + 2]['content'];
			if (preg_match('/^[A-Za-z0-9]+$/', $name) === 0 || preg_match('/^[a-z]{1}/', $name) === 0) {
				$file->addError('Method/function name must have camelCase style.', $pointer);
			}
		}
	}
}
