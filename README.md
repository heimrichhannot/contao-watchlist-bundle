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

All configuration options including the default values.

```yaml
huh_watchlist:
  content_elements:
    - download
    - downloads
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

1. Register your content element:

    ```yaml
    huh_watchlist:
      content_elements:
        - myContentElement
    ```
    
2. Optional: Add DCA fields to your element palette:

    ```php
    $dca['palettes']['myContentElement'] = str_replace(
       '{template_legend',
       '{watchlist_legend},disableWatchlist,overrideWatchlistConfig;{template_legend', 
       $dca['palettes']['myContentElement']
    );
    ```
    
3. Create an event listener for the `huh.watchlist.event.prepare_element` event. Use the event to create the AddToWatchlist button and add it to the element template. To generate the buttons, call `PartialTemplateBuilder::generate(new AddToWatchlistPartialTemplate())`. See example for more details.

    ```yaml
    # Register event listener
    services:
      MyVendor\MyBundle\EventListener\WatchlistPrepareElementListener:
        tags:
          - { name: kernel.event_listener, event: huh.watchlist.event.prepare_element }
    ```
    
    ```php
    <?php
    use HeimrichHannot\WatchlistBundle\Event\WatchlistPrepareElementEvent;
    use HeimrichHannot\WatchlistBundle\PartialTemplate\AddToWatchlistPartialTemplate;
    use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;
    
    class WatchlistPrepareElementListener
    {
        /** @var PartialTemplateBuilder */
        protected $templateBuilder;
        
        public function __construct(PartialTemplateBuilder $templateBuilder) 
        {
            $this->templateBuilder = $templateBuilder;
        }
        
        public function onHuhWatchlistEventPrepareElement(WatchlistPrepareElementEvent $event)
        {
            if ($event->getTemplate()->type === 'myContentElement') {
                $event->getTemplate()->addToWatchlistButton = $this->templateBuilder->generate(
                    new AddToWatchlistPartialTemplate(
                        $event->getConfiguration(),
                        'tl_content',
                        $event->getTemplate()->singleSRC,
                        $event->getTemplate()->fileName
                    )
                );
           }
        }
    }
    ```