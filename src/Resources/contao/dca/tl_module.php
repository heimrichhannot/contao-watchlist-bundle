<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2014 Heimrich & Hannot GmbH
 *
 * @package watchlist
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dc = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$dc['palettes'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist::MODULE_WATCHLIST] =
    '{title_legend},name,headline,type;{template_legend:hide},customTpl;{additionalSettingsLegend},useMultipleWatchlist,useDownloadLink,useGroupWatchlist,useWatchlistDurability,watchlistItemFile,watchlistItemEntity,downloadItemFile,downloadItemEntity;{protected_legend:hide},protected;{misc_legend},imgSize;{expert_legend:hide},guests,cssID,space';

$dc['palettes'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST] =
    '{title_legend},name,headline,type;{template_legend:hide},customTpl;{additionalSettingsLegend},usePublicLinkDurability;{protected_legend:hide},protected;{misc_legend},imgSize;{expert_legend:hide},guests,cssID,space';

/**
 * Subpalettes
 */
$dca['palettes']['__selector__'][] = 'useDownloadLink';
$dca['palettes']['__selector__'][] = 'useGroupWatchlist';
$dca['palettes']['__selector__'][] = 'useWatchlistDurability';
$dca['palettes']['__selector__'][] = 'usePublicLinkDurability';

$dca['subpalettes']['useDownloadLink']        = 'downloadLink';
$dca['subpalettes']['useGroupWatchlist']      = 'groupWatchlist';
$dca['subpalettes']['useWatchlistDurability'] = 'watchlistDurability';
$dca['subpalettes']['usePublicLinkDurability'] = 'publicLinkDurability';

/**
 * Fields
 */
$arrFields = [
    'useMultipleWatchlist'    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useMultipleWatchlist'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'useDownloadLink'         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useDownloadLink'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'downloadLink'            => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['downloadLink'],
        'exclude'   => true,
        'inputType' => 'pageTree',
        'eval'      => ['fieldType' => 'radio', 'tl_class' => 'clr'],
        'sql'       => "blob NULL",
    ],
    'useGroupWatchlist'       => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useGroupWatchlist'],
        'exclude'   => true,
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'groupWatchlist'          => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['groupWatchlist'],
        'exclude'    => true,
        'inputType'  => 'checkbox',
        'foreignKey' => 'tl_member_group.name',
        'eval'       => ['mandatory' => true, 'multiple' => true],
        'sql'        => "blob NULL",
        'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
    ],
    'useWatchlistDurability'  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useWatchlistDurability'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'watchlistDurability'     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['watchlistDurability'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
        'sql'       => "varchar(8) NOT NULL default ''",
    ],
    'usePublicLinkDurability' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['usePublicLinkDurability'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'publicLinkDurability'    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['publicLinkDurability'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
        'sql'       => "varchar(8) NOT NULL default ''",
    ],
    'watchlistItemFile' => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['watchlistItemFile'],
        'options_callback' => ['huh.watchlist.choice.watchlist_file', 'getChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50 clr',
            'notOverridable'     => true
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ],
    'watchlistItemEntity' => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['watchlistItemEntity'],
        'options_callback' => ['huh.watchlist.choice.watchlist_entity', 'getChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50',
            'notOverridable'     => true
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ],
    'downloadItemFile' => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['downloadItemFile'],
        'options_callback' => ['huh.watchlist.choice.download_file', 'getChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50 clr',
            'notOverridable'     => true
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ],
    'downloadItemEntity' => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['downloadItemEntity'],
        'options_callback' => ['huh.watchlist.choice.download_entity', 'getChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50',
            'notOverridable'     => true
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ]
];

$dc['fields'] = array_merge($dc['fields'], $arrFields);