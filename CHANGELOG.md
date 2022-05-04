# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased] - 2021-05-04
- Changed: updated add item template


## [3.2.0] - 2021-05-03
- Added: mod_watchlist_bs4.html5 template
- Added: WatchlistUtil::getWatchlistShareUrl()
- Changed: Moved add item code to service ([#4])

## [3.1.5] - 2021-03-15

- Changed: remove alert timer for the sake of accessibility
- Changed: modal content selector not stricted to .mod_watchlist or wrapper anymore

## [3.1.4] - 2022-01-13
- Fixed: add to list button not working for asynchronious loaded dom elements if not add to list button was on the page before

## [3.1.3] - 2021-12-21
- Fixed: manipulate rootfallback palette in contao 4.4

## [3.1.2] - 2021-12-20
- Fixed: clear watchlist button not working after changing watchlist content

## [3.1.1] - 2021-12-15
- Fixed: namespace in services_migration.yml

## [3.1.0] - 2021-12-14
- Added: WatchlistItemDataEvent
- Changed: migrated code to be compatible with contao 4.4 / Symfony 3.4
- Changed: migrated code to be compatible with php 7.3
- Changed: added some template blocks
- Fixed: getCurrentWatchlist()
- Fixed: foxy not enabled

## [3.0.2] - 2021-08-17

- Fixed: clear action

## [3.0.1] - 2021-07-27

- added symfony dependencies

## [3.0.0] - 2021-07-27

- major refactoring (migration from v2 included) -> see README.md for details (not all functions have been or will be
  ported to the new versions as many of them have been special interest)
- contao 4.9-only



[#4]: https://github.com/heimrichhannot/contao-watchlist-bundle/pull/4