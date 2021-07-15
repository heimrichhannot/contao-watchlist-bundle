<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_watchlist'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['tl_watchlist_item'],
        'switchToEdit' => true,
        'onsubmit_callback' => [
            [\HeimrichHannot\UtilsBundle\Dca\DcaUtil::class, 'setDateAdded'],
        ],
        'oncopy_callback' => [
            [\HeimrichHannot\UtilsBundle\Dca\DcaUtil::class, 'setDateAddedOnCopy'],
        ],
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
            'mode' => 1,
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
                'button_callback' => [\HeimrichHannot\WatchlistBundle\DataContainer\WatchlistContainer::class, 'editHeader'],
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['copy'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
                'button_callback' => [\HeimrichHannot\WatchlistBundle\DataContainer\WatchlistContainer::class, 'deleteArchive'],
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
        'default' => '{general_legend},name,authorType,author;',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist']['name'],
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
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(36) NOT NULL default ''",
        ],
    ],
];
