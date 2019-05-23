# Upgrade notices

## 1.0 to 2.0

### Config

The config structure has changes slightly and must be adapted:

```yaml
huh_watchlist:
  downloadFileItems:
    - { name: default, class: HeimrichHannot\WatchlistBundle\Item\DownloadItemFile }
```