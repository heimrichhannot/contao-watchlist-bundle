<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_user_group'];

/*
 * Palettes
 */
$dca['palettes']['default'] = str_replace('fop;', 'fop;{watchlist_legend},watchlists,watchlistp;', $dca['palettes']['default']);

/*
 * Fields
 */
$dca['fields']['watchlists'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['watchlists'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_watchlist_config.title',
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];

$dca['fields']['watchlistp'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['watchlistp'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];
