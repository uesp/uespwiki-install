MobileFrontend Extension
========================

The MobileFrontend extension adds a mobile view to your mediawiki instance.

Installation
------------

See [https://www.mediawiki.org/wiki/Extension:MobileFrontend\#Installation](https://www.mediawiki.org/wiki/Extension:MobileFrontend#Installation "https://www.mediawiki.org/wiki/Extension:MobileFrontend#Installation")

Configuration
-------------

See [https://www.mediawiki.org/wiki/Extension:MobileFrontend\#Configuration\_settings](https://www.mediawiki.org/wiki/Extension:MobileFrontend#Configuration_settings "https://www.mediawiki.org/wiki/Extension:MobileFrontend#Configuration_settings")

Development
-----------

### Coding conventions

Please follow the coding conventions of MobileFrontend: [https://www.mediawiki.org/wiki/MobileFrontend/Coding\_conventions](https://www.mediawiki.org/wiki/MobileFrontend/Coding_conventions "https://www.mediawiki.org/wiki/MobileFrontend/Coding_conventions")

#### Git hooks

Git hooks are provided in the dev-scripts directory to assist with adhering to JavaScript code standards, optimizing PNG files, etc. Running these hooks requires node.js, NPM, and grunt.

Install like so:

    make installhooks

If you are not running Vagrant, be sure to set your MEDIAWIKI\_URL env variable to your local index path, e.g. 'MEDIAWIKI\_URL=[http://localhost/index.php/](http://localhost/index.php/ "http://localhost/index.php/")'

### Committing

Commits are important as they give the reviewer more information to successfully review your code and find errors or potential problems you might not have thought of.

Commits are also useful when troubleshooting issues and refactoring. If it's not clear why a line of code is in the repository important bug fixes could be lost.

Commits should be as minor as possible. Please avoid removing unrelated console.log statements, fixing unrelated whitespace etc. do that in a separate commit which mentions the word cleanup.

First line commit should summarise the commit with bug it fixes if applicable. e.g. Fix problem with toggling see bug x. Second line should be blank. Third line should go into detail where necessary providing links to blog posts/other bugs to provide more background. Mention the platforms/browsers the change is for where necessary, e.g.:

-   'this is a problem on Android but not OSX see http://<url></url> which explains problem in detail'
-   'this is a workaround for a known bug in opera mobile see see http://<url></url>'

### Testing

#### Unit tests

To run the full test suite run:

    make tests

To run only PHP tests:

    make phpunit

To run only JS tests:

    make qunit

#### Selenium tests

For information on how to run Selenium tests please see README file in tests/browser directory.

### Releasing

A new version of MobileFrontend is released every two weeks. A developer needs to generate release notes and create a file with the title "RELEASE-NOTES-X.X.X.mediawiki" where "X.X.X" is the software version. Once a new release is due, the contents of the above file is moved to HISTORY.mediawiki and the file itself is deleted. Ideally, we need to create a bot similar to [https://wikitech.wikimedia.org/wiki/Jouncebot](https://wikitech.wikimedia.org/wiki/Jouncebot "https://wikitech.wikimedia.org/wiki/Jouncebot") that reads a calendar and pings a developer on \#wikimedia-mobile to remind them about a release.

#### Generating release notes

You can generate release notes by running (replace {branch name / commit SHA}):

    make releasenotes from={branch name / commit SHA} to={branch name / commit SHA}

Which will output a list of commits between two branches or commit SHAs.

#### Versioning

Adhere to [http://semver.org/](http://semver.org/ "http://semver.org/") when changing versions.

> Given a version number MAJOR.MINOR.PATCH, increment the:
>
> MAJOR version when you make incompatible API changes, MINOR version when you add functionality in a backwards-compatible manner, and PATCH version when you make backwards-compatible bug fixes.
