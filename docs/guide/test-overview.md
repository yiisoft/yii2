Testing
=======

Testing is an important part of software development. Whether we are aware of it or not, we conduct testing continuously.
For example, when we write a class in PHP, we may debug it step by step or simply use echo or die statements to verify
that implementation is correct. In case of web application we're entering some test data in forms to ensure the page
interacts with us as expected. The testing process could be automated so that each time when we need to test something,
we just need to call up the code that perform testing for us. This is known as automated testing, which is the main topic
of testing chapters.

The testing support provided by Yii includes:

- [Unit testing](test-unit.md) - verifies that a single unit of code is working as expected.
- [Functional testing](test-functional.md) - verifies scenarios from a user's perspective via browser emulation.
- [Acceptance testing](test-acceptance.md) - verifies scenarios from a user's perspective in a browser.


Test environment setup
----------------------

The unit testing supported provided by Yii requires [PHPUnit](http://phpunit.de/). In order to run functional and
acceptance tests you need [Codeception](http://codeception.com/).

### Installing PHPUnit

It's best to install PHPUnit globally. Since we have Composer we can do it with a single command:

```
composer global require "phpunit/phpunit=4.1.*"
```

After running the command you'll see "Changed current directory to /your/global/composer/dir" message. If it's the
first time you're installing a package globally you need to add `/your/global/composer/dir/vendor/bin/` to your `PATH`.

Now we're able to use `phpunit` from command line.

Check [PHPUnit documentation](http://phpunit.de/manual/current/en/installation.html) for more ways of installing it.

### Installing Codeception

Codeception can be installed globally the same way as PHPUnit:

```
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

Now we're able to use `codecept` from command line.
