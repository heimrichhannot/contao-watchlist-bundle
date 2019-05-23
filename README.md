# Contao Watchlist Bundle

A contao watchlist for download elements.


## Features

* use single or multiple watchlist
* generate link to watchlist (durability 30 days)
* define durability of the watchlist
* users of the same group can use same watchlist
* download watchlist items and watchlist
* [List Bundle](https://github.com/heimrichhannot/contao-list-bundle) support
* [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle) support
* [Notification Center](https://github.com/terminal42/contao-notification_center) support


## Setup

1. Install via composer
    
    ```
    composer require heimrichhannot/contao-watchlist-bundle
    ```
    
1. Update the database

1. Create an watchlist frontend module


## Developers

### Custom Watchlist window templates

Create twig templates with 'watchlist_window_' prefix. They should output the `headline` and `content` variable (be sure to output `content` as raw!).

Example: 
```twig
# src/Resources/views/watchlist/watchlist_window_default.html.twig

<div class="{{ class|default('') }}">
    {% if headlines is defined %}<h4>{{ headline }}</h4>{% endif %}
    {{ content|default|raw }}
</div>
```

### Modals

Modals typically need an starting event to be opened. Watchlist bundle comes with javascript events that can be used to archive these.

Example for bootstrap 4:

```js
// event.target is the module (.mod_watchlist) node

document.addEventListener('watchlist_content_ajax_success', (event) => {
    let modalElement = event.target.querySelector('.modal');
    if (null !== modalElement) {
        $(modalElement).modal();
    }
});
```

### Configuration

```yaml
huh_watchlist:
  downloadFileItems:
    - { name: default, class: HeimrichHannot\WatchlistBundle\Item\DownloadItemFile }
  downloadEntityItems:
    - { name: default, class: HeimrichHannot\WatchlistBundle\Item\DownloadItemEntity }
  watchlistFileItems:
    - { name: default, class: HeimrichHannot\WatchlistBundle\Item\WatchlistItemFile }
  watchlistEntityItems:
    - { name: default, class: HeimrichHannot\WatchlistBundle\Item\WatchlistItemEntity }
```

### Javascript Events

Event | Description
----- | -----------
watchlist_content_ajax_before |
watchlist_content_ajax_success |
watchlist_content_ajax_error |

### Add Item Button

```
$template->addWatchlist = Watchlist::getAddAction($array, $uuid, $multiple);
```

```
$array = [
	'name' => 'name of the item',
	'type' => 'type of the item (e.g. download)',
	'id' => 'id of the item'
];
```

* $uuid is the uuid of the \Contao\FileModel
* $multiple is true or false for the usage of the single or multiple watchlist