# Upgrade notices

## 1.0 to 2.0

### Database / DCA

* moved the watchlist configuration from module to own config table
* Change the field watchlist_config to watchlistConfig in tl_list_config and tl_reader_config

### Config

The config structure has changes slightly and must be adapted:

```yaml
huh_watchlist:
  downloadFileItems:
    - { name: default, class: HeimrichHannot\WatchlistBundle\Item\DownloadItemFile }
```

### List Bundle

* removed WatchlistDownloadListItem, use ImageConfigElementType and formatted field for uuid instead
* removed WatchlistListItem, use WatchlistConfigElementType instead
* removed WatchlistDownloadList, use FilterConfigElement auto_item with pid instead.