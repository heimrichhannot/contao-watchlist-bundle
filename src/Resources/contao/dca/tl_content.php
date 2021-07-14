<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$table = 'tl_content';

$dca = &$GLOBALS['TL_DCA'][$table];

$dca['palettes']['download'] = str_replace('{template_legend', '{watchlist_legend},disableWatchlist,overrideWatchlistConfig;{template_legend', $dca['palettes']['download']);

$dca['palettes']['__selector__'][] = 'overrideWatchlistConfig';
$dca['subpalettes']['overrideWatchlistConfig'] = 'watchlistConfig';

$dca['fields']['disableWatchlist'] = [
    'label' => &$GLOBALS['TL_LANG'][$table]['disableWatchlist'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50', 'submitOnChange' => false],
    'sql' => "char(1) NOT NULL default ''",
];

$dca['fields']['overrideWatchlistConfig'] = [
    'label' => &$GLOBALS['TL_LANG'][$table]['overrideWatchlistConfig'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$dca['fields']['watchlistConfig'] = [
    'label' => &$GLOBALS['TL_LANG'][$table]['watchlistConfig'],
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_watchlist_config.title',
    'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
    'eval' => ['tl_class' => 'long clr', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
    'wizard' => [
        ['huh.watchlist.data_container.module_container', 'editWatchlistWizard'],
    ],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
