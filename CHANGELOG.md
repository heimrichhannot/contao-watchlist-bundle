# Changelog

All notable changes to this project will be documented in this file.

## [3.0.0] - 2021-07-27

- major refactoring (migration from v2 included) -> see README.md for details (not all functions have been or will be
  ported to the new versions as many of them have been special interest)
- contao 4.9-only

## [2.0.0-alpha.0.6.1] - 2021-06-10

- fixes for contao 4.9

## [2.0.0-alpha.0.6.0] - 2021-06-08

- fixes for contao 4.9

## [2.0.0-alpha.0.5.0] - 2020-03-12

- fixed collecting watchlistItems
- fixed missing request_tokens

## [2.0.0-alpha.0.4.1] - 2020-02-26

- fixed request_tokens
- fixed errors in legacy script

## [2.0.0-alpha.0.4.0] - 2019-02-13

- merged branch scetch with master

## [2.0.0-alpha.0.3.0] - 2019-11-25

- added entity support
- fixed notification after successfull adding to watchlist

## [2.0.0-alpha.0.2.2] - 2019-10-25

- fixed missing file extensions in downloaded archives (#2)

## [2.0.0-alpha.0.2.1] - 2019-08-07

- open watchlist action now respects watchlist name setting

## [2.0.0-alpha.0.2.0] - 2019-08-07

- added WatchlistConfigElementType to add a "Add to watchlist" button to lists
- removed WatchlistListItem, WatchlistDownloadList, WatchlistDownloadListItem
- renamed namespace Module to FrontendModule
- made list bundle and notification center module optional dependencies
- removed modal dependency
- updated readme

## [2.0.0-alpha.0.1.0] - 2019-07-15

### Added

- readme

### Changed

- Watchlist configuration now lives in own entity
- made submissions an optional dependency
- configuration root key changed from huh:watchlist to huh_watchlist)

### Fixed

- ModuleWatchlist now working
- Ajax module classes used
- encore bundle legacy support
- hook issue

## [1.0.9] - 2019-02-14

### Changed

- added config fields `moduleId` and `watchlistId` to default submission palette

## [1.0.8] - 2019-02-14

### Fixed

- setting of privacyJumpTo

## [1.0.7] - 2019-02-08

### Changed

- use non minifyed bootstrap.native

## [1.0.6] - 2019-02-06

### Added

- langugage support for en,cs,pl,ru

## [1.0.5] - 2019-02-01

### Changed

- use notification instead of message for sending downloadLink

## [1.0.4] - 2019-01-29

### Changed

- starting time of durability of download link is now time()

## [1.0.3] - 2019-01-28

### Fixed

- `checkValidity` in `ModuleWatchlistDownload`

## [1.0.2] - 2019-01-25

### Fixed

- script error

## [1.0.1] - 2019-01-25

### Added

- form validation

## [1.0.0] - 2019-01-23

#### Added

- plain javascript
- send downloadLink with notification center
