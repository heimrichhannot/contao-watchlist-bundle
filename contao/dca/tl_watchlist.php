<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */
use Contao\DC_Table;
use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Dca\AuthorField;
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistContainer;
use HeimrichHannot\WatchlistBundle\Watchlist\AuthorType;

DateAddedField::register('tl_watchlist');

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
            'edit',
            'children',
            'delete',
            'show',
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
        'authorType' => [
            'exclude' => true,
            'filter' => true,
            'default' => AuthorType::NONE->value,
            'inputType' => 'select',
//            'options' => [
//                AuthorType::NONE,
//                AuthorType::MEMBER,
//                AuthorType::USER,
//                // session is only added if it's already set in the dca
//            ],
            'enum' => AuthorType::class,
            'eval' => ['doNotCopy' => true, 'submitOnChange' => true, 'mandatory' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(8) NOT NULL default 'none'",
        ],
        'author' => [
//            'exclude' => true,
//            'search' => true,
//            'filter' => true,
            'inputType' => 'select',
            'default' => '0',
//            'save_callback' => [function ($value, $dc) {
//                if (!$value) {
//                    return 0;
//                }
//
//                return $value;
//            }],
            'eval' => [
                'doNotCopy' => true,
                'chosen' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(64) NOT NULL default '0'",
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
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];
