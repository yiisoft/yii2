Automation
==========

There are some tasks that are done automatically when working on Yii:

- Generation of the classmap `classes.php` located under the framework root directory.
  Run `./build/build classmap` to generate it.

- Generation of the `@property` annotations in class files that describe properties introduced by getters and setters.
  Run `./build/build php-doc/property` to update them.

- Fixing of code style and other minor issues in phpdoc comments.
  Run `./build/build php-doc/fix` to fix them.
  Check the changes before you commit them as there may be unwanted changes because the command is not perfect.
  You may use `git add -p` to review the changes.

- Updating the Mime Type Magic file (`framework/helpers/mimeTypes.php`) from the Apache HTTPd repository.
  Run `./build/build mime-type` to update the file.

- The ordering of entries in the CHANGELOG file can be updated by running `./build/build release/sort-changelog framework`.

All of the above commands are included in the [release process](). They may be run between releases but it is not necessary.
