# Change Log

All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## [5.0.0] - 2022-02-09

### Added

- Added support for PHP 8.1.

### Changed

- **BREAKING**: All classes now have type-hinted properties.
- **BREAKING**: added return types for internal methods to remove deprecation messages for PHP 8.1.
- Minimum PHP version is now 7.4.

## [4.0.2] - 2021-02-27

**This release was forked from [neomerx/json-api](https://github.com/neomerx/json-api) at `v4.0.1`.**

### Fixed

- [#252](https://github.com/neomerx/json-api/issues/252) Fixed invoking relationship generator more than once when
  relationship is an include path, and is an empty generator.
