<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */
use Contao\DC_Table;
use Contao\DataContainer;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistContainer;

\HeimrichHannot\UtilsBundle\Dca\DateAddedField::register('tl_watchlist');

$GLOBALS['TL_DCA']['tl_watchlist'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_watchlist_item'],
        'switchToEdit' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['dateAdded'],
            'panelLayout' => 'filter;search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['edit'],
                'href' => 'table=tl_watchlist_item',
                'icon' => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['copy'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => [],
        'default' => '{general_legend},title,config,authorType,author,uuid;',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'uuid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['uuid'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 36, 'tl_class' => 'w50', 'mandatory' => true, 'readonly' => true],
            'sql' => "varchar(36) NOT NULL default ''",
        ],
        'config' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['config'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => [WatchlistContainer::class, 'getWatchlistConfigs'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];
