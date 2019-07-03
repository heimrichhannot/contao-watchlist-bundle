<?php

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Model;

/**
 * Class WatchlistConfigModel
 * @package HeimrichHannot\WatchlistBundle\Model
 *
 * @property string watchlistFrontendFramework
 */
class WatchlistConfigModel extends Model
{
    protected static $strTable = 'tl_watchlist_config';
}