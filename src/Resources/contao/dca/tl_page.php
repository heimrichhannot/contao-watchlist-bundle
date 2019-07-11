<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dca = &$GLOBALS['TL_DCA']['tl_page'];

$dca['palettes']['root'] = str_replace('{sitemap_legend', '{watchlist_legend},enableWatchlist;{sitemap_legend', $dca['palettes']['root']);

$dca['palettes']['__selector__'][] = 'enableWatchlist';
$dca['subpalettes']['enableWatchlist'] = 'watchlistConfig';

$dca['fields']['enableWatchlist'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_page']['enableWatchlist'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
    'sql'       => "char(1) NOT NULL default ''"
];
$dca['fields']['watchlistConfig'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_page']['watchlistConfig'],
    'exclude'    => true,
    'filter'     => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_watchlist_config.title',
    'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
    'eval'       => ['tl_class' => 'long clr', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
    'wizard'           => [
        ['huh.watchlist.data_container.module_container', 'editWatchlistWizard'],
    ],
    'sql'        => "int(10) unsigned NOT NULL default '0'"
];
