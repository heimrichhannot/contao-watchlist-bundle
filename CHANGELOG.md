# Changelog

All notable changes to this project will be documented in this file.

## [3.8.4] - 2023-06-06
- Fixed: items not added async to watchlist in dev mode

## [3.8.3] - 2023-06-06
- Fixed: issues with watchlist identification

## [3.8.2] - 2023-05-11
- Fixed: watchlist links not working when using binary uuid for link generation
- Fixed: items could not be added to watchlist in some cases

## [3.8.1] - 2023-05-02
- Fixed: warning

## [3.8.0] - 2023-05-02
- Added: encore contracts support
- Fixed: warnings
- Fixed: type error

## [3.7.1] - 2023-04-05
- Fixed: warning 

## [3.7.0] - 2022-11-25
- Updated: dependency to doctrine/DBAL ^3.5

## [3.6.0] - 2021-05-18
- Added: huh_watchlist_list_updated js event
- Changed: enhanced WatchlistUtil::getWatchlistItems()

## [3.5.0] - 2021-05-16
- Changed: store generated zip files in system tmp folder ([#5])
- Changed: use unique names for generated zip files ([#5])
- Changed: ShareList ist now FrontendModuleController again ([#5])
- Changed: drop php 7.3 support again ([#5])
- Fixed: now working in ShareList ([#5])

## [3.4.0] - 2021-05-10
- Added: pageModel request attribute to ajax request

## [3.3.1] - 2021-05-05
- Fixed: symfony 5 support

## [3.3.0] - 2021-05-05
- Changed: dropped symfony 3 and contao 4.4 support
- Changed: updated add item template
- Changed: allow symfony 5
- Fixed: class autoloading

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



[#5]: https://github.com/heimrichhannot/contao-watchlist-bundle/pull/5
[#4]: https://github.com/heimrichhannot/contao-watchlist-bundle/pull/4