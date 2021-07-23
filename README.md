# Contao Watchlist Bundle

This utility bundle offers a flexible watchlist for the Contao CMS.

## Features

- add files or arbitrary contao database entities like `tl_news` or `tl_member` to a watchlist
- watchlists can be viewed in the contao backend, as well
- ajax handling for a fluent usage (data is always kept up-to-date)
- integrating the watchlist is as easy as assigning a frontend module to a layout section and adding links by using
  insert tags
- download the files added to a watchlist as a ZIP archive
- contains clean templates without css styling and templates prepared with bootstrap 5 css classes
- share watchlists via link (also to public) and copy to clipboard

## Impressions

TODO

## Installation & configuration

1. Run `composer require heimrichhannot/contao-watchlist-bundle` and update your database.
1. Create a watchlist config (select bootstrap 5 templates if applicable).
1. **Important**: Assign the watchlist config to your root page.
1. Create a frontend module of type `watchlist` and add it to a layout area of your website so that it comes with each
   and every sub page. Select bootstrap 5 custom template if needed.
1. Identify the template you'd like to integrate a link for adding this item to the watch list. Common situations are:
    1. `ce_download.html5`
    1. `ce_downloads.html5`
    1. `news_full.html5`
    1. ...
1. Integrate the link for adding an item to the current watchlist to any template by using a simple insert tag:
   `{{watchlist_add_item_link}}` (see section "Insert tags" for further details on the parameters)

## Permission handling

A watchlist is private to its owner. Who is the owner depends on the context, the watchlist is used in:

Context | Who is the watchlist's owner?
--------|------------------------------
Anonymous | The current PHP session ID is used as the author
Frontend login available | The current frontend user (`member`) is used as the author

**Hint:** As watchlists are currently not transferrable from session to member, consider showing the watchlist only
after login. If you have no frontend login situation, you can ignore that, of course.

## Insert tags

The following new insert tags are available. These take into account the translated jumpTo url and alias.

Name | Example
-----|--------
`{{watchlist_add_item_link::file::<file uuid (binary or string)>::<optional: title>::<optional: watchlist uuid>}}` | `{{watchlist_add_item_link::file::2e6b6f54-e4af-11eb-b4fc-001e678385c6}}`
`{{watchlist_add_item_link::entity::<entity table>::<entity id>::<title>::<optional: entity url>::<optional: watchlist uuid>}}` | `{{watchlist_add_item_link::entity::tl_news::1::My headline::https://example.org/my-entity}}`
