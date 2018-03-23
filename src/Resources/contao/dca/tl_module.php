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
$dc['palettes'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist::MODULE_WATCHLIST] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{additionalSettingsLegend},useMultipleWatchlist,useDownloadLink,useGroupWatchlist,fileFieldEntity,fileFieldChildEntity;{protected_legend:hide},protected;{misc_legend},imgSize;{expert_legend:hide},guests,cssID,space';

$dc['palettes'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{additionalSettingsLegend};{protected_legend:hide},protected;{misc_legend},imgSize;{expert_legend:hide},guests,cssID,space';

/**
 * Subpalettes
 */
$dca['palettes']['__selector__'][]       = 'useDownloadLink';
$dca['palettes']['__selector__'][]       = 'useGroupWatchlist';
$dca['subpalettes']['useDownloadLink']   = 'downloadLink';
$dca['subpalettes']['useGroupWatchlist'] = 'groupWatchlist';

/**
 * Fields
 */
$arrFields = [
    'useMultipleWatchlist' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useMultipleWatchlist'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'useDownloadLink'      => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useDownloadLink'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'downloadLink'         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['downloadLink'],
        'exclude'   => true,
        'inputType' => 'pageTree',
        'eval'      => ['fieldType' => 'radio', 'tl_class' => 'clr'],
        'sql'       => "blob NULL",
    ],
    'useGroupWatchlist'    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useGroupWatchlist'],
        'exclude'   => true,
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'groupWatchlist'       => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['groupWatchlist'],
        'exclude'    => true,
        'inputType'  => 'checkbox',
        'foreignKey' => 'tl_member_group.name',
        'eval'       => ['mandatory' => true, 'multiple' => true],
        'sql'        => "blob NULL",
        'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
    ],
    'fileFieldEntity' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['fileFieldEntity'],
        'inputType' => 'multiColumnEditor',
        'eval'      => [
            'tl_class'          => 'long clr',
            'multiColumnEditor' => [
                'minRowCount' => 0,
                'sortable' => true,
                'fields' => [
                    'fieldName'   => [
                        'label'            => &$GLOBALS['TL_LANG']['tl_module']['fieldName'],
                        'exclude'          => true,
                        'filter'           => true,
                        'inputType'        => 'text',
                        'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true]
                    ],
                ],
            ],
        ],
        'sql'       => "blob NULL",
    ],
    'fileFieldChildEntity' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['fileFieldChildEntity'],
        'inputType' => 'multiColumnEditor',
        'eval'      => [
            'tl_class'          => 'long clr',
            'multiColumnEditor' => [
                'minRowCount' => 0,
                'sortable' => true,
                'fields' => [
                    'fieldName'   => [
                        'label'            => &$GLOBALS['TL_LANG']['tl_module']['fieldName'],
                        'exclude'          => true,
                        'filter'           => true,
                        'inputType'        => 'text',
                        'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true]
                    ],
                ],
            ],
        ],
        'sql'       => "blob NULL",
    ]
];

$dc['fields'] = array_merge($dc['fields'], $arrFields);