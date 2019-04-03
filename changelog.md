# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2019-04-02
### Changed
- Updated Relay to 2.0.0 official release
- Made it possible to specify action param classes as string only

## [2.1.0] - 2019-02-27
### Changed
- Changed provider for Whoops middleware so newer versions of Zend Diactoros can be used

## [2.0.0] - 2019-02-27
### Removed
- Removed `session_start()` call. You can start your own session if you want it
- Removed need to have `APP_BASE_PATH` defined
###  Added
- Added short route collector variable $r
- Added ability to disable action param middleware
- Added ability to disable CSRF middleware
- Added option to disable CSRF middleware only when in dev mode
- Updated kernel invoke arguments to accept incoming middleware
- Implemented emitter stack with conditional stream emitter
### Changed
- Moved CSRF exempt segments config to composer.json key

## [1.1.2] - 2019-02-24
### Fixed
- Fixed Kernel bug if DI didn't have errorClass, the wrong var was used for newing
### Changed
- Internal change, 100% code coverage for PHPUnit added

## [1.1.1] - 2019-01-16
### Fixed
- Fixed a dependency issue

## [1.1.0] - 2019-01-16
### Added
- Added the Request Helper

## [1.0.7] - 2019-01-13
### Fixed
- Added the HttpTwigExtension to the dependency injector

## [1.0.6] - 2019-01-13
### Changed
- Added Twig CSRF functions

## [1.0.5] - 2019-01-12
### Fixed
- Fixed an issue with getting route config file paths

## [1.0.4] - 2019-01-11
### Fixed
- Reconfigured config collection to use corbomite config collector

## [1.0.3] - 2019-01-05
### Fixed
- Fixed an issue with the Action Param Router

## [1.0.2] - 2019-01-01
### Changed
- Added twig function for throwing http error

## [1.0.1] - 2018-12-30
### Fixed
- Fixed an issue with config collection

## [1.0.0] - 2018-12-30
### New
- Initial Release
