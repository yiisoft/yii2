<?php

class Yii2_Sniffs_Files_EncodingSniff implements PHP_CodeSniffer_Sniff
{
	public function register()
	{
		return array(
			T_INLINE_HTML,
		);
	}

	public function process(PHP_CodeSniffer_File $file, $pointer)
	{
		if ($pointer !== 0) {
			return;
		}
		$tokens = $file->getTokens();
		foreach ($this->byteOrderMarks() as $encoding => $byteOrderMark) {
			$hex = bin2hex(substr($tokens[$pointer]['content'], 0, strlen($byteOrderMark) / 2));
			if ($hex === $byteOrderMark) {
				$file->addError('Detected %s byte order mark (BOM) at the beginning of the file. BOM are not allowed!',
					$pointer, '', array($encoding));
				break;
			}
		}
	}

	private function byteOrderMarks()
	{
		return array(
			'UTF-8' => 'efbbbf',
			'UTF-16 (BE)' => 'feff',
			'UTF-16 (LE)' => 'fffe',
		);
	}
}
