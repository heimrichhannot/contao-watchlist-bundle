<?php

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Model;
use Contao\PageModel;

/**
 * Class WatchlistConfigModel
 * @package HeimrichHannot\WatchlistBundle\Model
 *
 * @property int $id
 * @property int $tstamp
 * @property int $dateAdded
 * @property string $title
 * @property string $watchlistFrontendFramework
 * @property bool $useMultipleWatchlist
 * @property bool $useGroupWatchlist
 * @property bool $groupWatchlist
 * @property bool $useWatchlistDurability
 * @property string $watchlistDurability
 * @property bool $useGlobalDownloadAllAction
 * @property bool $disableDownloadAll
 * @property bool $overrideWatchlistTitle
 * @property string $watchlistItemFile
 * @property string $watchlistItemEntity
 * @property string $downloadItemFile
 * @property string $downloadItemEntity
 * @property string $watchlistTitle
 */
class WatchlistConfigModel extends Model
{
    protected static $strTable = 'tl_watchlist_config';

    /**
     * Return a watchlist config if enabled and set for current page tree.
     *
     * @param PageModel $page
     * @return WatchlistConfigModel|null
     */
    public static function findByPage(PageModel $page): ?WatchlistConfigModel
    {
        $rootPage = PageModel::findByPk($page->rootId);
        if (!$rootPage)
        {
            return null;
        }
        if (!$rootPage->enableWatchlist) {
            return null;
        }
        return static::findByPk($rootPage->watchlistConfig);
    }
}