Testing
=======

Testing is an important part of software development. Whether we are aware of it or not, we conduct testing continuously.
For example, when we write a class in PHP, we may debug it step by step or simply use `echo` or `die` statements to verify
the implementation works according to our initial plan. In the case of a web application, we're entering some test data
in forms to ensure the page interacts with us as expected.

The testing process could be automated so that each time when we need to verify something, we just need to call up
the code that does it for us. The code that verifies the result matches what we've planned is called *test* and
the process of its creation and further execution is known as *automated testing*, which is the main topic of these
testing chapters.


## Developing with tests

Test-Driven Development (TDD) and Behavior-Driven Development (BDD) are approaches of developing
software by describing behavior of a piece of code or the whole feature as a set of scenarios or tests before
writing actual code and only then creating the implementation that allows these tests to pass verifying that intended
behavior is achieved.

The process of developing a feature is the following:

- Create a new test that describes a feature to be implemented.
- Run the new test and make sure it fails. It is expected since there's no implementation yet.
- Write simple code to make the new test pass.
- Run all tests and make sure they all pass.
- Improve code and make sure tests are still OK.

After it's done the process is repeated again for another feature or improvement. If the existing feature is to be changed,
tests should be changed as well.

> Tip: If you feel that you are losing time doing a lot of small and simple iterations, try covering more by your
> test scenario so you do more before executing tests again. If you're debugging too much, try doing the opposite.

The reason to create tests before doing any implementation is that it allows us to focus on what we want to achieve
and fully dive into "how to do it" afterwards. Usually it leads to better abstractions and easier test maintenance when
it comes to feature adjustments or less coupled components.

So to sum up the advantages of such approach are the following:

- Keeps you focused on one thing at a time which results in improved planning and implementation.
- Results in test-covering more features in greater detail i.e. if tests are OK most likely nothing's broken.

In the long term it usually gives you a good time-saving effect.

## When and how to test

While the test first approach described above makes sense for long term and relatively complex projects it could be overkill
for simpler ones. There are some indicators of when it's appropriate:

- Project is already large and complex.
- Project requirements are starting to get complex. Project grows constantly.
- Project is meant to be long term.
- The cost of the failure is too high.

There's nothing wrong in creating tests covering behavior of existing implementation.

- Project is a legacy one to be gradually renewed.
- You've got a project to work on and it has no tests.

In some cases any form of automated testing could be overkill:

- Project is simple and isn't getting anymore complex.
- It's a one-time project that will no longer be worked on.

Still if you have time it's good to automate testing in these cases as well.

## Further reading

- Test Driven Development: By Example / Kent Beck. ISBN: 0321146530.
