<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\WatchlistModuleController;
use HeimrichHannot\WatchlistBundle\EventListener\Contao\LoadDataContainerListener;
use HeimrichHannot\WatchlistBundle\EventListener\Contao\ReplaceInsertTagsListener;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
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
$GLOBALS['FE_MOD']['miscellaneous'][WatchlistModuleController::TYPE] = WatchlistModule::class;