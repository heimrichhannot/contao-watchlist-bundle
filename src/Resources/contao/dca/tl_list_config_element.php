<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_list_config_element'];

$dca['palettes']['__selector__'][] = 'overrideWatchlistConfig';
$dca['palettes']['__selector__'][] = 'watchlistType';
$dca['subpalettes']['overrideWatchlistConfig'] = 'watchlistConfig';
$dca['subpalettes']['watchlistType_'.\HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE] = 'fileField';

$dca['fields']['overrideWatchlistConfig'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['overrideWatchlistConfig'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$dca['fields']['watchlistConfig'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['watchlistConfig'],
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_watchlist_config.title',
    'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
    'eval' => ['tl_class' => 'w50 clr wizard', 'includeBlankOption' => true, 'chosen' => true],
    'wizard' => [
        [\HeimrichHannot\WatchlistBundle\DataContainer\ModuleContainer::class, 'editWatchlistWizard'],
    ],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$dca['fields']['watchlistType'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['watchlistType'],
    'inputType' => 'select',
    'options' => [
        \HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE,
        \HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::WATCHLIST_ITEM_TYPE_ENTITY,
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_list_config_element']['watchlistType'],
    'exclude' => true,
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50 autoheight clr'],
    'sql' => "varchar(32) NOT NULL default ''",
];

$dca['fields']['fileField'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['fileField'],
    'inputType' => 'select',
    'options_callback' => function (DataContainer $dc) {
        return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getFields($dc);
    },
    'exclude' => true,
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight clr'],
    'sql' => "varchar(32) NOT NULL default ''",
];
$dca['fields']['titleField'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['titleField'],
    'inputType' => 'select',
    'options_callback' => function (DataContainer $dc) {
        return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getFields($dc);
    },
    'exclude' => true,
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
    'sql' => "varchar(32) NOT NULL default ''",
];
