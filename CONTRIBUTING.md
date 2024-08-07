# CONTRIBUTING


Contributions are welcome and we look forward to seeing what you can bring to the project.

We do reserve the right to reject changes that we feel will not benefit users or take the project in a direction we do
not wish to take.
We recommend discussing any large changes with us prior to making your changes, you can do this via an issue or email
if you prefer.

## Issues

Issues should include a title and clear description with as much relevant information as possible.

If you'd like to discuss a potential change, you can open an issue or reach out to our open-source team via
**open-source@ukfast.co.uk** who will get back to you as soon as possible.

If you think you have identified a security vulnerability, please contact our team via **security@ukfast.co.uk**
instead of using the issue tracker.

## Submitting Changes

Before submitting your [pull request](https://help.github.com/en/articles/about-pull-requests),
please make sure that the coding standards are respected and that all tests are passing. 

Pull requests should provide an overview of the changes made so it is clear what you are trying to achieve, this will
help us when reviewing your changes.

Commit messages should be clear, we don't mind one-line messages for small changes 
but larger changes should follow [The Seven Rules](https://chris.beams.io/posts/git-commit/) set by Chris Beams. 

We don't mind squashing small changes to tidy things up but please don't squash an entire branch as this can hinder code
reviews.

## Coding Standards

### All Standards

All coding standards can be checked by running `composer standards:check` and any issues which can be automatically
resolved can be fixed by running `composer standards:fix`. 

### PSR-12

This project adheres to the [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/).

### PHP CodeSniffer && PHP Code Beautifier

PSR-12 coding standards can be checked with `composer phpcs` and issues which can be fixed automatically can be fixed by
running `composer phpcs:fix`.

### PHP Mess Detector

We use [PHP Mess Detector](https://phpmd.org/) with the rules at `./phmd/rulset.xml` to ensure the package contains high quality code.
This can be run with `composer phpmd`.

### Rector

We use [Rector](https://getrector.org/) to ensure the package is using the latest PHP features.
This can be checked with `composer rector` and any issues can be fixed with `composer rector:fix`.

### Larastan

We use [Larastan](https://github.com/larastan/larastan) (which is a Laravel wrapper for [PHPStan](https://phpstan.org/))
to perform static analysis and help to identify potential bugs in the package before it makes it into a public version.
This can be run with `composer larastan`.

## Testing

Please ensure all new functionality is matched with appropriate tests, we will advise if we feel more is needed.

Tests can be run with `composer test`.
