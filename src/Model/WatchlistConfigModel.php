<?php

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Model;
use Contao\PageModel;

/**
 * Class WatchlistConfigModel
 * @package HeimrichHannot\WatchlistBundle\Model
 *
 * @property string watchlistFrontendFramework
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