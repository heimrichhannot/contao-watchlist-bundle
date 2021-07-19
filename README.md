# Contao Watchlist Bundle

This utility bundle offers a flexible watchlist for the Contao CMS.

## Features

- add files or arbitrary contao database entities like `tl_news` or `tl_member` to a watchlist
- integrating the watchlist is as easy as assigning a frontend module to a layout section
- download the files added to a watchlist as a ZIP archive

## Impressions

## Installation & configuration

1. Run `composer require heimrichhannot/contao-watchlist-bundle` and update your database.

## Usage

1. You can add the link for adding an item to the watchlist to any template by using a simple insert tag:
   `{{watchlist_add_link::}}`

## Insert tags

The following new insert tags are available. These take into account the translated jumpTo url and alias.

Name | Example
-----|--------
`{{watchlist::<id>::<language>}}` | `{{watchlist::1::de}}`
