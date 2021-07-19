<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_page'];

/*
 * Palettes
 */
foreach (['root', 'rootfallback'] as $palette) {
    \Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addLegend('watchlist_legend', 'sitemap_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
        ->addField('watchlistConfig', 'watchlist_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
        ->applyToPalette($palette, 'tl_page');
}

/**
 * Fields.
 */
$fields = [
    'watchlistConfig' => [
        'label' => &$GLOBALS['TL_LANG']['tl_watchlistConfig']['watchlistConfig'],
        'exclude' => true,
        'filter' => true,
        'inputType' => 'select',
        'foreignKey' => 'tl_watchlist_config.title',
        'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);
