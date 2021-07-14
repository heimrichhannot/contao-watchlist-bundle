# Contao Watchlist Bundle

A contao watchlist for download elements. It enables users to collect items (e.g. downloads, images) into an watchlist and download them later or altogether as zip file.

> Version 2 of this bundle is an major rewrite of this bundle and not complete backwards compatible. A migration tool is not given, but you can view the major changes in the [UPGRADE](./UPGRADE.md) document.

> This bundle is still in alpha, as not all parts of the bundle are rewritten and tested. Currently only the parts for using it with content elements is mostly done and well tested.

## Features

* users can collect item into watchlist
* download watchlist items and watchlist
* use single or multiple watchlist
* generate link to watchlist
* define durability of the watchlist
* users of the same group can use same watchlist
* Easily add watchlist support to your entity/modules/contentelement/list
    * download and downloads content element supported out of the box
* Frontend output is high customizable to be used with your frontend framework
    * a simple base layout and bootstrap 4 already bundled
* [List Bundle](https://github.com/heimrichhannot/contao-list-bundle) support 
* [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle) support
* [Notification Center](https://github.com/terminal42/contao-notification_center) support


## Setup

1. Install via composer
    
    ```
    composer require heimrichhannot/contao-watchlist-bundle
    ```
    Make sure version 2 is used.
    
1. Update the database.

1. Create an watchlist config (Contao Backend -> System -> Watchlist configuration).

1. Enable watchlist in your root page and select the created watchlist config.

1. Create an watchlist module and select the created config.

1. Optional: If you have custom templates for download(s) content element, output `$this->addToWatchlistButton['html']`


## Usage

### List bundle 

To add an "add to watchlist" button to your list entries, add the watchlist list config element type to the list configuration and output the corresponding template variable.

If you want to create a watchlist list (output the content of an watchlist from list bundle), use the auto_item filter config element with pid for a filter on tl_watchlist_item.


## Developers

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

### Add custom frontend layouts

> Due alpha state of this bundle, no all templates supported by the frontend framework system. Old templates must be overridden the "contao way".

1. Create an frontend framework class extending `AbstractWatchlistFrontendFramework` and register it as service with `huh.watchlist.framework` service tag.

1. Create twig templates for your framework with framework type suffix, e.g. `watchlist_window_base.html.twig` for base framework or `watchlist_window_bs4.html.twig` for bootstrap 4.

    Available Templates (please consider the base templates for available template variables:
    
    Template                   | Description
    -------------------------- | -----------
    open_watchlist_window      | The open watchlist button (used in the watchlist module)
    watchlist_action           | Action button. Used for 'add to watchlist' and 'download all' action.
    watchlist_item_parent_list | Renders the parent `pagePath` if grouped  
    watchlist_window           | The watchlist window 
    
    Not defined template for a framework will fallback to the base templates, if the Framework class extends `AbstractWatchlistFrontendFramework`.
    
1. Create js event listener for adding watchlist button count `watchlist_create_count_element_[FRAMEWORK TYPE]`, if non exist. Event is dispatched on every element that need an updated count.

    Example: 
    
    ```js
    // init event listener
    class MyEventListener {
        init () 
        {
            document.addEventListener('watchlist_create_count_element_base', this.onWatchlistCreateCountElementBase);
        }
        
        // event listener method
        onWatchlistCreateCountElementBase(event)
        {
            let badge = document.createElement('span');
            badge.setAttribute('class', event.detail.cssClass);
            badge.textContent = event.detail.count;
            event.target.appendChild(badge);
        }
    }
    ```

1. Optional: create js event listener for open watchlist action, e.g. trigger the event toggle

    Example:

    ```js
    class MyEventListener {
        init () 
        {
            document.addEventListener('watchlist_window_open_bs4', this.onWatchlistWindowOpenBs4);
        }
        
        /**
         * @param {CustomEvent} event
         */
        onWatchlistWindowOpenBs4(event)
        {
            let modalElement = event.detail.container.querySelector('.modal');
            if (null !== modalElement) {
                $(modalElement).modal();
            }
        }
    }
    ```

### Add watchlist support to your entity

1. Register your content element:

    ```yaml
    huh_watchlist:
      content_elements:
        - myContentElement
    ```
    
2. Optional: Add DCA fields to your element palette:

    ```php
    // tl_content.php
 
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
                    new AddToWatchlistActionPartialTemplate(
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