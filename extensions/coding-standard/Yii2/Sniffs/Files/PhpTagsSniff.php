<?php

class Yii2_Sniffs_Files_PhpTagsSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_OPEN_TAG,
			T_CLOSE_TAG,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
		$tokens = $file->getTokens();
		if (strpos($tokens[$pointer]['content'], '<?') === 0 &&
			strpos($tokens[$pointer]['content'], '<?php') === false) {
			$file->addError('Short open PHP tags are not allowed.', $pointer);
		}
		if (strpos($tokens[$pointer]['content'], '<%') === 0) {
			$file->addError('ASP style open PHP tags are not allowed.', $pointer);
		}
	}
}
