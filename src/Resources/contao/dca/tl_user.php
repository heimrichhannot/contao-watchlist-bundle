<?php

$dca = &$GLOBALS['TL_DCA']['tl_user'];

/**
 * Palettes
 */
$dca['palettes']['extend'] = str_replace('fop;', 'fop;{watchlist_legend},watchlists,watchlistp;', $dca['palettes']['extend']);
$dca['palettes']['custom'] = str_replace('fop;', 'fop;{watchlist_legend},watchlists,watchlistp;', $dca['palettes']['custom']);

/**
 * Fields
 */
$dca['fields']['watchlists'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['watchlists'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_watchlist_config.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL"
];

$dca['fields']['watchlistp'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['watchlistp'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];