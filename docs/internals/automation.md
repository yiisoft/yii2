Automation
==========

There are some tasks that are done automatically when working on Yii:

- Updating the Mime Type Magic file (`framework/helpers/mimeTypes.php`) from the Apache HTTPd repository.
  Run `./build/build mime-type` to update the file.

- The ordering of entries in the CHANGELOG file can be updated by running `./build/build release/sort-changelog framework`.

All of the above commands are included in the [release process](). They may be run between releases but it is not necessary.
