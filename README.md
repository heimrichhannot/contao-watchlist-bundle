# Contao Watchlist Bundle

This utility bundle offers a flexible watch list for the Contao CMS.

## Features

- add files or arbitrary contao database entities like `tl_news` or `tl_member` to a watch list
- watch lists can be viewed in the contao backend, as well
- ajax handling for a fluent usage (data is always kept up-to-date)
- integrating the watch list is as easy as assigning a frontend module to a layout section and adding links by using
  insert tags
- download the files added to a watch list as a ZIP archive
- contains clean templates without css styling and templates prepared with bootstrap 5 css classes
- share watch lists via link (also to public) and copy to clipboard

## Impressions

TODO

## Installation & configuration

1. Run `composer require heimrichhannot/contao-watchlist-bundle` and update your database.
1. Create a watch list config (select bootstrap 5 templates if applicable).
1. **Important**: Assign the watch list config to your root page.
1. Create a frontend module of type `watchlist` and add it to a layout area of your website so that it comes with each
   and every sub page. Select bootstrap 5 custom template if needed.
1. Identify the template you'd like to integrate a link for adding this item to the watch list. Common situations are:
    1. `ce_download.html5`
    1. `ce_downloads.html5`
    1. `news_full.html5`
    1. ...
1. Integrate the link for adding an item to the current watch list to any template by using a simple insert tag:
   `{{watchlist_add_item_link}}` (see section "Insert tags" for further details on the parameters)

## Item types

A watch list in the context of this bundle can have multiple types of items:

1. `File`: Any file in the contao file manager
1. `Entity`: Any database record (aka "entity"), e.g. `tl_news`, `tl_member` -> downloads aren't possible at the moment

## Sharing watch lists

1. Do the steps mentioned in "Installation & configuration"
1. Create a target page for shared watch lists.
1. In your watch list config activate the sharing feature.
1. Create the list module for the share page. You have 2 options:
    1. For simpler needs and file-only watch lists: Create a frontend module of the type "watchlist_share_list". This
       module fulfills simple needs. Image watch list items are downloadable, entity items are reachable by link.
    1. For more complex needs (like internal areas, filtering, ...): Create a list
       using [heimrichhannot/contao-list-bundle](https://github.com/heimrichhannot/contao-list-bundle) and a reader
       using [heimrichhannot/contao-reader-bundle](https://github.com/heimrichhannot/contao-reader-bundle).
        1. Activate the option `actAsWatchlistShareTarget` in the list and reader configs.
        1. Make sure, the `detailsUrl` in the list item template contains the `watchlist` GET parameter. For example,
           you could create a block for the `detailsUrl`, extend your default template and override the `detailsUrl`:
           
           ```twig
           {% block detailsUrl %}
           {{ detailsUrl }}?watchlist={{ app.request.get('watchlist') }}
           {% endblock %}
           ```
1. Place the module created in the previous step on the page created in the second page.

## Permission handling

A watch list is private to its owner. Who is the owner depends on the context, the watch list is used in:

Context | Who is the watch list's owner?
--------|------------------------------
Anonymous | The current PHP session ID is used as the author
Frontend login available | The current frontend user (`member`) is used as the author

**Hint:** As watch lists are currently not transferable from session to member, consider showing the watch list only
after login. If you have no frontend login situation, you can ignore that, of course.

## Insert tags

The following new insert tags are available. These take into account the translated jumpTo url and alias.

Name | Example
-----|--------
`{{watchlist_add_item_link::file::<file uuid (string)>::<optional: title>::<optional: watch list uuid>}}` | `{{watchlist_add_item_link::file::2e6b6f54-e4af-11eb-b4fc-001e678385c6}}`
`{{watchlist_add_item_link::entity::<entity table>::<entity id>::<title>::<optional: entity url>::<optional: preview file uuid (string)>::<optional: watch list uuid>}}` | `{{watchlist_add_item_link::entity::tl_news::1::My headline::https://example.org/my-entity::2e6b6f54-e4af-11eb-b4fc-001e678385c6}}`
