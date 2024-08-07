# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2024-08-07

### Added

- Enforcing [PSR-12](https://www.php-fig.org/psr/psr-12/) in CI via
  [PHP CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)
  [#91](https://github.com/ans-group/laravel-health-check/pull/91) by [@phily245](https://github.com/phily245)
- Enforcing [PHP Mess Detector](https://phpmd.org/) in CI to keep the code tidier
  [#92](https://github.com/ans-group/laravel-health-check/pull/92) by [@phily245](https://github.com/phily245)
- Enforcing [PHPStan](https://phpstan.org/) [level 6](https://phpstan.org/user-guide/rule-levels) via
  [Larastan](https://github.com/larastan/larastan) in CI to keep the code more reliable
  [#97](https://github.com/ans-group/laravel-health-check/pull/97)
  [#98](https://github.com/ans-group/laravel-health-check/pull/98)
  [#99](https://github.com/ans-group/laravel-health-check/pull/99)
  [#100](https://github.com/ans-group/laravel-health-check/pull/100)
  [#101](https://github.com/ans-group/laravel-health-check/pull/101)
  [#102](https://github.com/ans-group/laravel-health-check/pull/102) by [@phily245](https://github.com/phily245)
- Enforcing [RectorPHP](https://getrector.com/) checks in CI to keep the code more maintainable
  [#197](https://github.com/ans-group/laravel-health-check/pull/97) by [@phily245](https://github.com/phily245)
- Adding stricter typing
  [#87](https://github.com/ans-group/laravel-health-check/pull/87) by [@phily245](https://github.com/phily245)
- Documenting the new tooling in the contributing guide
  [#103](https://github.com/ans-group/laravel-health-check/pull/103) by [@phily245](https://github.com/phily245)

### Removed

- Dropping support for < Laravel 10.0 to only support versions with bug/security fixes
  [#82](https://github.com/ans-group/laravel-health-check/pull/82) by [@phily245](https://github.com/phily245) 
- Dropping support for PHP < 8.1 to match Laravel 10's support
  [#82](https://github.com/ans-group/laravel-health-check/pull/82) by [@phily245](https://github.com/phily245)

### Fixed

- Upgrading the FTP file check to use flysystem v3
  [#89](https://github.com/ans-group/laravel-health-check/pull/89) by [@phily245](https://github.com/phily245)
- Upgrading the package security health check so to not use a deprecated package
  [#88](https://github.com/ans-group/laravel-health-check/pull/88) by [@srichter](https://github.com/srichter)
  & [@phily245](https://github.com/phily245)

## [1.15.0] - 2024-07-17

### Added

- The ability to set a default problem HTTP code [#79](https://github.com/ans-group/laravel-health-check/pull/79) by [@Dziamid-Harbatsevich](https://github.com/Dziamid-Harbatsevich)
- A healthcheck artisan command [#70](https://github.com/ans-group/laravel-health-check/pull/70) by [@nick322](https://github.com/nick322)

## [1.14.0] - 2023-02-20

### Changed

- Stops PHPUnit 10 being installed [#76](https://github.com/ans-group/laravel-health-check/pull/76) by [@rbibby](https://github.com/rbibby)

## [1.13.5] - 2023-01-04

### Updated

- Tests now run on PHP 8.2 [#74](https://github.com/ans-group/laravel-health-check/pull/74) by [@rbibby](https://github.com/rbibby)
- `uniqid` now uses the `$more_entropy` flag [#73](https://github.com/ans-group/laravel-health-check/pull/73) by [@Lotykun](https://github.com/Lotykun)

## [1.13.4] - 2022-07-11

### Added

- Allow plugins for update-helper package [#69](https://github.com/ans-group/laravel-health-check/pull/69) by [@rbibby](https://github.com/rbibby)

## [1.13.3] - 2022-02-22

### Added

- Added support for Laravel 9

## [1.13.2] - 2021-12-21

### Changed

- Updated tests to run on more versions
 
## [1.13.1] - 2021-10-13

### Added

- Add support for clusters in the RedisHealthCheck [#60](https://github.com/ukfast/laravel-health-check/pull/60) by [@adamkirk](https://github.com/adamkirk)

## [1.13.0] - 2021-07-22

### Added

- Add FtpHealthCheck [#59](https://github.com/ukfast/laravel-health-check/pull/59) by [@rbibby](https://github.com/rbibby)

## [1.12.2] - 2021-06-18

### Fixed

- Fix env check default value [#58](https://github.com/ukfast/laravel-health-check/pull/58) by [Michel Tomas](https://github.com/superbiche)

## [1.12.1] - 2021-05-19

### Fixed

- Fix Backwards Incompatible Config Additions [#56](https://github.com/ukfast/laravel-health-check/issues/56) by [phily245](https://github.com/phily245)

## [1.12.0] - 2021-05-17

### Added

- Allow overriding health and ping route paths [#53](https://github.com/ukfast/laravel-health-check/pull/53) by [Andrew Warren-Love](https://github.com/awarrenlove)

## [1.11.0] - 2021-05-17

### Added

- Added PHP8 to the test runner [#52](https://github.com/ukfast/laravel-health-check/pull/52) by [Yozhef](https://github.com/Yozhef)
- Added support for degraded health checks [#54](https://github.com/ukfast/laravel-health-check/issues/54) by [Andrew Warren-Love](https://github.com/awarrenlove)

## [1.10.2] - 2021-04-09

### Fixed

- Added backward compatibility fixes to cache health check by making it use Carbon for times [#50](https://github.com/ukfast/laravel-health-check/pull/51) by [nick332](https://github.com/nick332)

## [1.10.1] - 2021-03-17

### Fixed

- Fix namespace of BasicAuth in README file [#49](https://github.com/ukfast/laravel-health-check/pull/49) by [mohamed-akef](https://github.com/mohamed-akef)

## [1.10.0] - 2021-02-24

### Added

- Add health check make command [#48](https://github.com/ukfast/laravel-health-check/pull/48) by [mawaishanif](https://github.com/mawaishanif)

## [1.9.1] - 2021-02-15

### Fixed

- Fix named route bug in Lumen.

## [1.9.0] - 2021-02-12

### Added

- Add named route support [#46](https://github.com/ukfast/laravel-health-check/pull/46) by [@leganz](https://github.com/leganz)

## [1.8.0] - 2020-11-05

### Added

- Add SchedulerHealthCheck [#43](https://github.com/ukfast/laravel-health-check/pull/43) by [@rbibby](https://github.com/rbibby)

## [1.7.2] - 2020-10-20

### Added

- Add test case for base path functionality

## [1.7.1] - 2020-10-19

### Fixed

- Use base_path when route registering routes [#41](https://github.com/ukfast/laravel-health-check/pull/41) by [@palpalani](https://github.com/palpalani)

## [1.7.0] - 2020-10-13

### Added

- Add MigrationUpToDateHealthCheck. [#38](https://github.com/ukfast/laravel-health-check/pull/38) from [@timohund](https://github.com/timohund)

## [1.6.0] - 2020-10-08

### Added

- Add PackageSecurityHealthCheck. [#36](https://github.com/ukfast/laravel-health-check/pull/36) from [@srichter](https://github.com/srichter)

### Fixed

- Fix StorageHealthCheckTest filename. [#35](https://github.com/ukfast/laravel-health-check/pull/35) from [@srichter](https://github.com/srichter)

## [1.5.0] - 2020-10-03

### Added

- Add CODE_OF_CONDUCT.md
- Add StorageHealthCheck. [#32](https://github.com/ukfast/laravel-health-check/pull/32) from [@srichter](https://github.com/srichter)
- Increase test coverage for HttpHealthCheck. [#34](https://github.com/ukfast/laravel-health-check/pull/34) from [@srichter](https://github.com/srichter)
- Increase Test coverage for CacheHealthCheck. [#33](https://github.com/ukfast/laravel-health-check/pull/33) from [@srichter](https://github.com/srichter)

### Changed

- Update README.md
- Update CONTRIBUTING.md
- Update tests to use assertSame instead of assertEquals. [#31](https://github.com/ukfast/laravel-health-check/pull/31) from [@wesolowski](https://github.com/wesolowski)


## [1.4.0] - 2020-09-26

### Added

- Add AddHeaders middleware

## [1.3.0] - 2020-09-08

### Added

- Add support for Laravel v8.x

## [1.2.1] - 2020-08-24

### Added

- Add Facade alias

### Changed

- Update README.md

### Fixed

- Fixed facade all method to return a collection

## [1.2.0] - 2020-08-21

### Added

- Add CacheHealthCheck
- Add HealthCheck facade

## [1.1.0] - 2020-08-21

### Added

- Add BasicAuth middleware

### Changed

- Run CI on pull requests

## [1.0.4] - 2020-04-26

### Changed

- Use empty string to connect to default database. [#8](https://github.com/ukfast/laravel-health-check/pull/8) from [@RootPrivileges](https://github.com/RootPrivileges)


## [1.0.3] - 2020-03-10

### Added

- Add support for Laravel/Illuminate 7.x [#6](https://github.com/ukfast/laravel-health-check/pull/6) from [@rbibby](https://github.com/rbibby)


## [1.0.2] - 2020-03-05

### Added

- CI status badge to README.md

### Changed

- README.md changes

### Fixed

- Fix config publishing in HealthCheckServiceProvider. [#4](https://github.com/ukfast/laravel-health-check/pull/4) from [@rbibby](https://github.com/rbibby)

## [1.0.1] - 2020-02-17

### Added

- GitHub Actions CI workflow

### Changed 

- README.md changes

## [1.0.0] - 2020-02-14

### Added

- Initial commit

[unreleased]: https://github.com/ukfast/laravel-health-check/compare/v1.13.4...HEAD
[1.13.4]: https://github.com/ukfast/laravel-health-check/tree/v1.13.4
[1.13.3]: https://github.com/ukfast/laravel-health-check/tree/v1.13.3
[1.13.2]: https://github.com/ukfast/laravel-health-check/tree/v1.13.2
[1.13.1]: https://github.com/ukfast/laravel-health-check/tree/v1.13.1
[1.13.0]: https://github.com/ukfast/laravel-health-check/tree/v1.13.0
[1.12.2]: https://github.com/ukfast/laravel-health-check/tree/v1.12.2
[1.12.1]: https://github.com/ukfast/laravel-health-check/tree/v1.12.1
[1.12.0]: https://github.com/ukfast/laravel-health-check/tree/v1.12.0
[1.11.0]: https://github.com/ukfast/laravel-health-check/tree/v1.11.0
[1.10.2]: https://github.com/ukfast/laravel-health-check/tree/v1.10.2
[1.10.1]: https://github.com/ukfast/laravel-health-check/tree/v1.10.1
[1.10.0]: https://github.com/ukfast/laravel-health-check/tree/v1.10.0
[1.9.1]: https://github.com/ukfast/laravel-health-check/tree/v1.9.1
[1.9.0]: https://github.com/ukfast/laravel-health-check/tree/v1.9.0
[1.8.0]: https://github.com/ukfast/laravel-health-check/tree/v1.8.0
[1.7.2]: https://github.com/ukfast/laravel-health-check/tree/v1.7.2
[1.7.1]: https://github.com/ukfast/laravel-health-check/tree/v1.7.1
[1.7.0]: https://github.com/ukfast/laravel-health-check/tree/v1.7.0
[1.6.0]: https://github.com/ukfast/laravel-health-check/tree/v1.6.0
[1.5.0]: https://github.com/ukfast/laravel-health-check/tree/v1.5.0
[1.4.0]: https://github.com/ukfast/laravel-health-check/tree/v1.4.0
[1.3.0]: https://github.com/ukfast/laravel-health-check/tree/v1.3.0
[1.2.1]: https://github.com/ukfast/laravel-health-check/tree/v1.2.1
[1.2.0]: https://github.com/ukfast/laravel-health-check/tree/v1.2.0
[1.1.0]: https://github.com/ukfast/laravel-health-check/tree/v1.1.0
[1.0.4]: https://github.com/ukfast/laravel-health-check/tree/v1.0.4
[1.0.3]: https://github.com/ukfast/laravel-health-check/tree/v1.0.3
[1.0.2]: https://github.com/ukfast/laravel-health-check/tree/v1.0.2
[1.0.1]: https://github.com/ukfast/laravel-health-check/tree/v1.0.1
[1.0.0]: https://github.com/ukfast/laravel-health-check/tree/v1.0.0
