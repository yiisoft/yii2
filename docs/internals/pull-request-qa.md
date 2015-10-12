Pull request quality assurance
==============================

When checking if PR could be merged or not the following criteria should be considered among others:

- There should be either an issue linked to PR or PR should have a good description on what is it fixing or adding.
- Unit tests. Not mandatory but very appreciated. These should fail without the code that the PR is fixing. 
- CHANGELOG entry should present. It should be put into next release section, ordered by issue type and number.
Nicknames of people responsible should present.
- [Code style](core-code-style.md) and [views code style](view-code-style.md) should be OK. These could be fixed while
  merging if the person merging prefers it that way.
