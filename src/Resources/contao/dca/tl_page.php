<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$dca = &$GLOBALS['TL_DCA']['tl_page'];

/*
 * Palettes
 */

$pm = PaletteManipulator::create()
    ->addLegend('watchlist_legend', 'sitemap_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('watchlistConfig', 'watchlist_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page');
if (isset($GLOBALS['TL_DCA']['tl_page']['palettes']['rootfallback'])) {
    $pm->applyToPalette('rootfallback', 'tl_page');
}

/**
 * Fields.
 */
$fields = [
    'watchlistConfig' => [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['watchlistConfig'],
        'exclude' => true,
        'filter' => true,
        'inputType' => 'select',
        'foreignKey' => 'tl_watchlist_config.title',
        'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);
