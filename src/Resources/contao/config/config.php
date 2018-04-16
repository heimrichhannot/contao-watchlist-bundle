<?php

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, [
    'miscellaneous' => [
        HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist::MODULE_WATCHLIST                           => 'HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist',
        HeimrichHannot\WatchlistBundle\Module\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST => 'HeimrichHannot\WatchlistBundle\Module\ModuleWatchlistDownloadList',
    ],
]);

$GLOBALS['TL_HOOKS']['getPageLayout'][] = ['huh.watchlist.ajax_manager', 'ajaxActions'];


/**
 * JavaScipt
 */

if(\Contao\System::getContainer()->get('huh.utils.container')->isFrontend())
{
//    $GLOBALS['TL_JAVASCRIPT']['contao-watchlist-bundle'] = 'bundles/heimrichhannotcontaowatchlist/js/jquery.watchlist.min.js|static';
}

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_watchlist']      = 'HeimrichHannot\WatchlistBundle\Model\WatchlistModel';
$GLOBALS['TL_MODELS']['tl_watchlist_item'] = 'HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel';

