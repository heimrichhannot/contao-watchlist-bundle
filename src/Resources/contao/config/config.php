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

if(\Contao\System::getContainer()->get('huh.utils.container')->isFrontend() && !class_exists(\HeimrichHannot\EncoreBundle\DependencyInjection\EncoreExtension::class))
{
    $GLOBALS['TL_JAVASCRIPT']['contao-watchlist-bundle'] = 'bundles/heimrichhannotcontaowatchlist/js/jquery.watchlist.min.js|static';
}

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_watchlist']      = 'HeimrichHannot\WatchlistBundle\Model\WatchlistModel';
$GLOBALS['TL_MODELS']['tl_watchlist_item'] = 'HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel';

$GLOBALS['AJAX'][\HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_GROUP] = [
    'actions' => [
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SHOW_MODAL_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_ADD_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_DATA,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_NEW_WATCHLIST_ADD_ITEM_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_DATA,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_NAME,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_DURABILITY,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DELETE_ITEM_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DELETE_WATCHLIST_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SELECT_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_UPDATE_WATCHLIST_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_MODULE_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_ADD_ITEM_TO_SELECTED_WATCHLIST => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_WATCHLIST_ITEM_DATA,
            ],
            'optional' => [],
        ],
    ],
];