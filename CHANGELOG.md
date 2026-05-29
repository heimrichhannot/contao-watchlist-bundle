# Changelog

All notable changes to this project will be documented in this file.

## [4.0.0-beta2.0] - 2026-05-28
- Added: WatchlistItem::getTitle() and WatchlistItem::getFileSize() ([#12](https://github.com/heimrichhannot/contao-watchlist-bundle/pull/12))
- Changed: modernized share module ([#12](https://github.com/heimrichhannot/contao-watchlist-bundle/pull/12))
  - use new template system [POSSIBLE BC BREAK!] 
  - update templates for easier variant creation [POSSIBLE BC BREAK!]
- Changed: WatchlistItem::getImage() now features a size parameter ([#12](https://github.com/heimrichhannot/contao-watchlist-bundle/pull/12))
- Fixed: typo in share module template name ([#12](https://github.com/heimrichhannot/contao-watchlist-bundle/pull/12))

## [4.0.0-beta1.2] - 2026-05-28
- Fixed: download links in shared watchlist not working

## [4.0.0-beta1.1] - 2026-05-20
- Changed: allow Uuid in WatchlistLinkGenerator::generateAddFileLink()
- Fixed: exception when using Uuid with watchlist_add_file twig function

## [4.0.0-beta1] - 2026-02-11
- Added: twig function `watchlist_add_file` 
- Changed: requires contao 5.3 or higher
- Changed: adjust some templates for modern contao template system
- Changed: a lot of refactoring