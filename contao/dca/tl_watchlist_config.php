<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */
use Contao\System;
use Contao\BackendUser;
use HeimrichHannot\WatchlistBundle\DataContainer\WatchlistConfigContainer;

$GLOBALS['TL_DCA']['tl_watchlist_config'] = [
    'config' => [
        'dataContainer' => 'Table',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
        'onsubmit_callback' => [
            [WatchlistConfigContainer::class, 'setDateAdded'],
        ],
        'oncopy_callback' => [
            [WatchlistConfigContainer::class, 'setDateAddedOnCopy'],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 1,
            'fields' => ['title'],
            'headerFields' => ['title'],
            'panelLayout' => 'filter;sort,search,limit',
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
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => [
            'addShare',
        ],
        'default' => '{general_legend},title;{image_legend},imgSize;{share_legend},addShare;{template_legend},watchlistContentTemplate,insertTagAddItemTemplate;',
    ],
    'subpalettes' => [
        'addShare' => 'shareJumpTo',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'insertTagAddItemTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['insertTagAddItemTemplate'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => [WatchlistConfigContainer::class, 'getInsertTagAddItemTemplates'],
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'watchlistContentTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['watchlistContentTemplate'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => [WatchlistConfigContainer::class, 'getWatchlistContentTemplates'],
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'imgSize' => [
            'exclude' => true,
            'inputType' => 'imageSize',
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'options_callback' => static fn() => System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(BackendUser::getInstance()),
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'addShare' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['addShare'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'shareJumpTo' => [
            'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['shareJumpTo'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50', 'mandatory' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];
