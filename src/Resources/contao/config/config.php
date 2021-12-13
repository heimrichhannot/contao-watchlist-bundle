<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\ShareListModuleController;
use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\WatchlistModuleController;
use HeimrichHannot\WatchlistBundle\EventListener\Contao\LoadDataContainerListener;
use HeimrichHannot\WatchlistBundle\EventListener\Contao\ReplaceInsertTagsListener;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Module\ShareListModule;
use HeimrichHannot\WatchlistBundle\Module\WatchlistModule;

$GLOBALS['BE_MOD']['system']['watchlist'] = [
    'tables' => ['tl_watchlist', 'tl_watchlist_item'],
];

$GLOBALS['BE_MOD']['system']['watchlist_config'] = [
    'tables' => ['tl_watchlist_config'],
];

/*
 * Model
 */
$GLOBALS['TL_MODELS']['tl_watchlist'] = WatchlistModel::class;
$GLOBALS['TL_MODELS']['tl_watchlist_item'] = WatchlistItemModel::class;
$GLOBALS['TL_MODELS']['tl_watchlist_config'] = WatchlistConfigModel::class;

/*
 * Frontend modules
 */
$GLOBALS['FE_MOD']['list']['miscellaneous'][ShareListModuleController::TYPE] = ShareListModule::class;
$GLOBALS['FE_MOD']['list']['miscellaneous'][WatchlistModuleController::TYPE] = WatchlistModule::class;

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [LoadDataContainerListener::class, '__invoke'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ReplaceInsertTagsListener::class, '__invoke'];