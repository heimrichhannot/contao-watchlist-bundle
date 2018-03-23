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
    $GLOBALS['TL_JAVASCRIPT']['contao-watchlist-bundle'] = 'bundles/heimrichhannotcontaowatchlist/js/jquery.watchlist.min.js|static';
}

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_watchlist']      = 'HeimrichHannot\WatchlistBundle\Model\WatchlistModel';
$GLOBALS['TL_MODELS']['tl_watchlist_item'] = 'HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel';


/**
 * Ajax Actions
 */
//$GLOBALS['AJAX'][\HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_GROUP] = [
//    'actions' => [
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SHOW_MODAL_ACTION          => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_ADD_ACTION                 => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION      => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_CID,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_UPDATE_ACTION              => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION    => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION       => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DELETE_ACTION              => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DELETE_ALL_ACTION          => [
//            'arguments' => [],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SELECT_ACTION              => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_MULTIPLE_ADD_ACTION        => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_CID,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_NAME,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_DURABILITY,
//            ],
//            'optional'  => [],
//        ],
//        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_MULTIPLE_SELECT_ADD_ACTION => [
//            'arguments' => [
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_CID,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
//                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_NAME,
//            ],
//            'optional'  => [],
//        ],
//    ],
//];