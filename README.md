# Contao Watchlist Bundle

A contao watchlist for download elements.


## Features

* use single or multiple watchlist
* generate link to watchlist (durability 30 days)
* define durability of the watchlist
* users of the same group can use same watchlist
* download watchlist items and watchlist
* Easily add watchlist support to your entity/modules/contentelement
    * download and downloads content element supported out of the box
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

### Add watchlist support to your entity

1. Add required field to your dca. To help you with this, we have created a helper class, see example for the download elements:

    ```php
    // src/Resources/contao/dca/tl_content.php
    
    // Adds the fields to the dca and add the field to the download palette before the template section. 
    $dca = \HeimrichHannot\WatchlistBundle\Helper\DcaHelper::addDcaFields('tl_content, '{template_legend', 'download');
    // Add fields to the downloads palette before the template section
    \HeimrichHannot\WatchlistBundle\Helper\DcaHelper::addDcaMapping($dca, '{template_legend', 'downloads');
    ```
    
2. Output the buttons in your template:
    
    ```php 
    //src/Resources/contao/templates/elements/ce_downloads.html5
    
    // ...
    <?php foreach ($this->files as $file): ?>
        // ...
        <?php echo \Contao\System::getContainer()->get('huh.watchlist.template_manager')
            ->generateAddToWatchlistButtonForContentElement($this->getData(), $file['uuid']); ?>
    <?php endforeach; ?>
    ```
    
    For more advanced options use `generateAddToWatchlistButtonForTemplate()` or `getAddToWatchlistButton()` methods.