<?php

class Yii2_Sniffs_VariablesProperties_CamelCaseSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_VARIABLE,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
		$tokens = $file->getTokens();
		if ($tokens[$pointer]['content'][0] === '$') {
			$name = substr($tokens[$pointer]['content'], 1);
			if (preg_match('/^[A-Za-z0-9_]+$/', $name) === 0 || preg_match('/^[a-z_]{1}/', $name) === 0) {
				$file->addError('Variable/property name must have camelCase style.', $pointer);
			}
		}
	}
}
