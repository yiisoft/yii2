<?php

return [
	// string, required, root directory of all source files
	'sourcePath' => __DIR__,
	// string, required, root directory containing message translations.
	'messagePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
	// array, required, list of language codes that the extracted messages
	// should be translated to. For example, ['zh-CN', 'de'].
	'languages' => ['de'],
	// string, the name of the function for translating messages.
	// Defaults to 'Yii::t'. This is used as a mark to find the messages to be
	// translated. You may use a string for single function name or an array for
	// multiple function names.
	'translator' => 'Yii::t',
	// boolean, whether to sort messages by keys when merging new messages
	// with the existing ones. Defaults to false, which means the new (untranslated)
	// messages will be separated from the old (translated) ones.
	'sort' => false,
	// boolean, whether the message file should be overwritten with the merged messages
	'overwrite' => true,
	// boolean, whether to remove messages that no longer appear in the source code.
	// Defaults to false, which means each of these messages will be enclosed with a pair of '@@' marks.
	'removeUnused' => false,
	// array, list of patterns that specify which files/directories should be processed.
	// If empty or not set, all files/directories will be processed.
	// A path matches a pattern if it contains the pattern string at its end. For example,
	// '/a/b' will match all files and directories ending with '/a/b';
	// and the '.svn' will match all files and directories whose name ends with '.svn'.
	// Note, the '/' characters in a pattern matches both '/' and '\'.
	// If a file/directory matches both a pattern in "only" and "except", it will NOT be processed.
	'only' => ['.php'],
	// array, list of patterns that specify which files/directories should NOT be processed.
	// If empty or not set, all files/directories will be processed.
	// Please refer to "only" for details about the patterns.
	'except' => [
		'.svn',
		'.git',
		'.gitignore',
		'.gitkeep',
		'.hgignore',
		'.hgkeep',
		'/messages',
	],
	// Generated file format. Can be either "php" or "po".
	'format' => 'php',
];
