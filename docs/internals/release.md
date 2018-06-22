Releasing a new version
=======================

The list of steps needed to make a release of the framework has grown over time and became
hard to manage manually, so we have created a command line tool to ensure no step is forgotten.

Release steps overview
----------------------

- ...

The release command
-------------------

These steps are automated in the [release console command](../../build/controllers/ReleaseController.php)
which is included in the framework development repository.

The release command can be invoked using the Yii application contained in the `build` directory of 
the framework:

    ./build/build help release  # run this in framework repo root

> Info: You can run the command with the `--dryRun` option to see what it would do. Using this option,
> no changes will be made and no commits or tags will be created or pushed.

### Requirements

The release command depends on the development environment introduced in
the [Git Workflow Document](git-workflow.md#extensions), i.e. the application 
templates must be located under `/apps/` and extensions must be located under `/extensions/`.
This structure is preferably created using the `dev/app` and `dev/ext` commands.

e.g. install an extension:

    ./build/build dev/ext authclient

or an application:

    ./build/build dev/app basic

This installation will ensure that the extension will use the same framework code that is in the current
repositories state.

### Version overview

To get an overview over the versions of framework and extensions, you can run

    ./build/build release/info

You may run it with `--update` to fetch tags for all repos to get the newest information.

### Make a release

Making a framework release includes the following commands (apps are always released together with the framework):

    ./build/build release framework
    ./build/build release app-basic
    ./build/build release app-advanced

Making an extension release includes only one command (e.g. for redis):

    ./build/build release redis

The default release command will release a new minor version from the currently checked out branch.
To release another version than the default, you have to specify it using the `--version` option, e.g.
`--version=3.0.0`, or `--version=3.0.0-alpha`.


#### Release a new major version e.g. 3.0.0

Releasing a new major version includes a branch change as described in the
[versioning policy](versions.md).
The following describes an example of releasing version `3.0.0` which has been
developed on the `3.0` branch derived from `master`. `master` has contained the `2.0.x` versions
before.

- create a new branch `3.0` from `master`
- ensure composer.json does not contain a branch alias on this branch anymore.
- merge necessary changes from `master` to `3.0`
- point `master` to the lastest commit on `3.0`
- adjust composer.json branch alias for master to `3.0.x-dev`.
- delete `3.0` branch

Now check out `master` and run the release command with the `--version=3.0.0` option. 

