# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[unreleased]: https://github.com/ukfast/laravel-health-check/compare/v1.7.1...HEAD
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
