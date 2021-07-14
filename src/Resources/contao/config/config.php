<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['BE_MOD']['system']['watchlist_item'] = [
    'tables' => ['tl_watchlist', 'tl_watchlist_item'],
];

$GLOBALS['BE_MOD']['system']['watchlist'] = [
    'tables' => ['tl_watchlist'],
];

/*
 * Model
 */
$GLOBALS['TL_MODELS']['tl_watchlist'] = \HeimrichHannot\WatchlistBundle\Model\WatchlistModel::class;
$GLOBALS['TL_MODELS']['tl_watchlist_item'] = \HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::class;
