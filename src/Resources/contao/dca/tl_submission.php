<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_submission'];

$dca['palettes']['default'] .= 'moduleId,watchlistId';

$dca['fields']['moduleId'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_submission']['moduleId'],
    'exclude' => true,
    'inputType' => 'hidden',
    'load_callback' => [['huh.watchlist.data_container.module_container', 'getModuleId']],
    'sql' => "int(3) unsigned NOT NULL default '0'",
];

$dca['fields']['watchlistId'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_submission']['module'],
    'exclude' => true,
    'inputType' => 'hidden',
    'load_callback' => [['huh.watchlist.data_container.module_container', 'getWatchlistId']],
    'sql' => "int(3) unsigned NOT NULL default '0'",
];
