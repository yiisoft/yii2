#!/bin/sh

TMPDIR=./tmp/
# - run aspell to list all misspelled (unknown to aspell) words
#   new words can be added to .aspell.en_US.pws to make them known

mkdir -p ${TMPDIR}guide
for file in guide/*.md
do
	# - sed: filter all links to Yii class names
	# - php-regex: filter all code blocks
	cat $file | \
		# filter all links to Yii class names
		sed -r 's/\[\[[A-Za-z0-9\\:$()_]+(\||\]\])/[[]]/g' | \
		# filter all code blocks
		php -r 'echo preg_replace("/```\w*\n.+?```/s", "", stream_get_contents(STDIN));' | \
		# filter all inline code elements
		php -r 'echo preg_replace("/`[^`]+`/", "``", stream_get_contents(STDIN));' | \
		# filter markdown links
		php -r 'echo preg_replace("/(\\[[^\\[]+\\])\s*\\([^\\)]+\\)/", "\1", stream_get_contents(STDIN));' > ${TMPDIR}$file
done

misspelledWords=$(cat ${TMPDIR}guide/intro-*.md | aspell -l en_US --personal=./.aspell.en_US.pws list |sort |uniq)

EXITCODE=0
# find and print the words by file and line number
for w in $misspelledWords
do
	EXITCODE=1
	echo $w
	grep -rnIw --color=auto $w ${TMPDIR}guide/
done

rm -r ${TMPDIR} 

if [ $EXITCODE -eq 0 ] ; then
	echo "spellcheck successful."
fi
exit $EXITCODE
