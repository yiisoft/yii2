Testing
=======

Testing is an important part of software development. Whether we are aware of it or not, we conduct testing continuously.
For example, when we write a class in PHP, we may debug it step by step or simply use echo or die statements to verify
that implementation is correct. In case of web application we're entering some test data in forms to ensure the page
interacts with us as expected. The testing process could be automated so that each time when we need to test something,
we just need to call up the code that perform testing for us. This is known as automated testing, which is the main topic
of testing chapters.

[Test-Driven Development (TDD)](http://en.wikipedia.org/wiki/Test-driven_development) is an approach of developing software when you write your code in repeatable steps that verifies produced features
with needed assertions.

Test - block of program that verifies correct or not correct behavior of needed feature. All tests should be automated to be able to make them
run often and fast.

Main life cycle of this approach contains following steps:

- Create a new test that covers a feature to be implemented. The test is expected to fail at its first execution because the feature has yet to be implemented;
- Run test and make sure the new test fails;
- Write simple code to make the new test pass;
- Run all tests and make sure they all pass;
- Refactor the code in needed way and make sure the tests still pass.

Repeat step 1 to 5 to push forward the functionality implementation. Depending on your skills you can increase or decrease size
of code that you change between steps and tests run. If you feel that you are doing a lot of simple changes and loosing your time then it is
time to make steps bigger, but if you started to spend a lot of time on debug when writing tests it is a good sign to make steps slower.
Mainly you should write code only before test to be able to understand what it does and to avoid leaks of code implementation into tests. This approach
is called `Test First`. It is a vital part of testing to understand that code implementation should not leak to your tests, thus you will
be able to concentrate more on abstractions of code and features, rather then particular implementation. Tests that have such leaks becoming
hard to maintain and understand, and as a conclusion they are deleted. However depending on your understanding of TDD and your skills of writing good decoupled code you can write tests after
the implementation is done, there is nothing wrong about it, while you are understanding what you are doing and why.

There are a lot of reasons why you should use TDD, below are the common ones:

- it saves your time on debug in long term;
- it keeps you concentrated only on one thing - currently developing feature, and not in whole project. Thus you only write code that you really need;
- it lets you know immediately if something will go wrong or functionality of some features will be broken;  
- it lets you understand the use case of feature you are developing now and you are able to build systems with clear interfaces;
- it lets you understand dependencies between code and how to solve them to make system decoupled, particular with [Inversion Of Control](http://en.wikipedia.org/wiki/Inversion_of_control) principle.

TDD can help you to build good decoupled systems, but it will not replace some basic knowledges of refactoring and structuring code, so you
should not consider it as a `silver bullet`. It is almost always that you will think about code, features and specifications before writing any test case.
There are other software approaches to help you with it, like : [Behavior Driven Development (BDD)](http://en.wikipedia.org/wiki/Behavior-driven_development) and [Domain Driven Development (DDD)](https://en.wikipedia.org/wiki/Domain-driven_design).

However it is not always necessary to use TDD in your project, you should consider below cases of when you can or should use it:

- you are working on big project or project that gets bigger;
- you are working on long term project;
- you have time for TDD, since tests should not be written once and forgotten, they should be also crafted as other code;
- you have correct environment that allows you to use TDD. It can be testing frameworks, fixtures frameworks and so on;
- you are working on project with legacy code that should be rewritten fully or partial;
- your team understand the value of bringing good quality code to business.

If you are working on simple CRUD or small projects usually you dont need TDD, since it can take a half of all time in developing with small projects.
When working on legacy code it is very hard to apply TDD due to hard system coupling and wrong architectural design issues, however by isolating some code blocks 
and working with them one by one in small parts you can rewrite it much faster with TDD than without it, since you will be sure everything is works correctly and you can move on.

Below are listed books where you can find more answers for your TDD quesitons:

- "Test-driven Development by Example",  Kent Beck;

To know what testing frameworks and environments is bundled or supported by Yii2 you can see [Test Environment Setup](test-environment-setup.md) guide.
