<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['watchlist_config'] = [
    'tables' => ['tl_watchlist_config'],
];

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'watchlists';
$GLOBALS['TL_PERMISSIONS'][] = 'watchlistp';


/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, [
    'miscellaneous' => [
        HeimrichHannot\WatchlistBundle\FrontendModule\ModuleWatchlist::MODULE_WATCHLIST                           => \HeimrichHannot\WatchlistBundle\FrontendModule\ModuleWatchlist::class,
        HeimrichHannot\WatchlistBundle\FrontendModule\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST => \HeimrichHannot\WatchlistBundle\FrontendModule\ModuleWatchlistDownloadList::class,
    ],
]);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getPageLayout']['huh.watchlist']     = ['huh.watchlist.listeners.hooks', 'onGetPageLayout'];
$GLOBALS['TL_HOOKS']['parseTemplate']['huh.watchlist']     = ['huh.watchlist.listeners.hooks', 'onParseTemplate'];

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
$GLOBALS['TL_MODELS']['tl_watchlist']      = \HeimrichHannot\WatchlistBundle\Model\WatchlistModel::class;
$GLOBALS['TL_MODELS']['tl_watchlist_item'] = \HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::class;
$GLOBALS['TL_MODELS']['tl_watchlist_config'] = \HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel::class;

/**
 * AJAX
 */

$GLOBALS['AJAX'][\HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_GROUP] = [
    'actions' => [
        'watchlistAjaxController' => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_NEW_WATCHLIST_ADD_ITEM_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA

            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA

            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DELETE_ITEM_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_DELETE_WATCHLIST_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SELECT_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_UPDATE_WATCHLIST_ACTION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_ADD_ITEM_TO_SELECTED_WATCHLIST => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SEND_DOWNLOAD_LINK_NOTIFICATION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_SEND_DOWNLOAD_LINK_AS_NOTIFICATION => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ],
        \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_WATCHLIST_LOAD_DOWNLOAD_LINK_FORM => [
            'arguments' => [
                \HeimrichHannot\WatchlistBundle\Manager\AjaxManager::XHR_PARAMETER_DATA
            ],
            'optional' => [],
        ]
    ],
];

/**
 * Notification Center
 */

foreach ($GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] as $strType => $arrTypes) {
    foreach ($arrTypes as $strConcreteType => &$arrType) {
        foreach (['recipients', 'email_text', 'email_html'] as $strName) {
            if (isset($arrType[$strName])) {
                $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'][$strType][$strConcreteType][$strName] = array_unique(
                    array_merge(
                        [
                            'downloadLink',
                            'salutation_*',
                            'form_*'
                        ],
                        $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'][$strType][$strConcreteType][$strName]
                    )
                );
            }
        }
    }
}