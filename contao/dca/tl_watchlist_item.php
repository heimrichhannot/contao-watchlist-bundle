<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */
use Contao\DC_Table;
use Contao\DataContainer;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer;

$GLOBALS['TL_DCA']['tl_watchlist_item'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_watchlist',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
        'onsubmit_callback' => [
            [WatchlistItemContainer::class, 'setDateAdded'],
        ],
        'oncopy_callback' => [
            [WatchlistItemContainer::class, 'setDateAddedOnCopy'],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['dateAdded'],
            'headerFields' => ['title'],
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => [WatchlistItemContainer::class, 'listChildren'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => [
            'type',
        ],
        'default' => '{general_legend},title,type;',
        WatchlistItemContainer::TYPE_FILE => '{general_legend},title,type;{reference_legend},file;{context_legend},page,autoItem;',
        WatchlistItemContainer::TYPE_ENTITY => '{general_legend},title,type;{reference_legend},entityTable,entity,entityUrl,entityFile;{context_legend},page,autoItem;',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_watchlist.title',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => DataContainer::SORT_DAY_DESC,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['type'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => WatchlistItemContainer::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'file' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['file'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'tl_class' => 'clr',
                'filesOnly' => true,
                'fieldType' => 'radio',
                'mandatory' => true,
            ],
            'sql' => 'binary(16) NULL',
        ],
        'entityTable' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['entityTable'],
            'inputType' => 'select',
            'options_callback' => [WatchlistItemContainer::class, 'getDataContainers'],
            'eval' => [
                'tl_class' => 'w50',
                'chosen' => true,
                'submitOnChange' => true,
                'mandatory' => true,
                'includeBlankOption' => true,
            ],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'entity' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['entity'],
            'inputType' => 'select',
            'options_callback' => [WatchlistItemContainer::class, 'getEntities'],
            'eval' => ['tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'mandatory' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'entityUrl' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['entityUrl'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 long', 'rgxp' => 'url'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'entityFile' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['entityFile'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'tl_class' => 'clr',
                'filesOnly' => true,
                'fieldType' => 'radio',
                'mandatory' => true,
            ],
            'sql' => 'binary(16) NULL',
        ],
        'page' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['page'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'pageTree',
            'eval' => ['rgxp' => 'digit', 'maxlength' => 10, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'autoItem' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_item']['autoItem'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
