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

\Contao\System::loadLanguageFile('tl_submission_archive');
\Contao\Controller::loadDataContainer('tl_submission_archive');
/**
 * Palettes
 */
$dc['palettes'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist::MODULE_WATCHLIST] =
    '{title_legend},name,headline,type;
    {template_legend:hide},watchlistWindowTemplate,customTpl;
    {additionalSettingsLegend},useMultipleWatchlist,useGroupWatchlist,useWatchlistDurability,useGlobalDownloadAllAction,watchlistItemFile,watchlistItemEntity,downloadItemFile,downloadItemEntity,disableDownloadAll,overrideWatchlistTitle,overrideTogglerTitle;
    {download_legend},useDownloadLink, downloadLinkUseNotification;
    {protected_legend:hide},protected;
    {misc_legend},imgSize;
    {expert_legend:hide},guests,cssID,space';

$dc['palettes'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST] =
    '{title_legend},name,headline,type;{template_legend:hide},customTpl;{additionalSettingsLegend},listConfig,usePublicLinkDurability;{protected_legend:hide},protected;{misc_legend},imgSize;{expert_legend:hide},guests,cssID,space';

/**
 * Subpalettes
 */
$dca['palettes']['__selector__'][] = 'useDownloadLink';
$dca['palettes']['__selector__'][] = 'useGroupWatchlist';
$dca['palettes']['__selector__'][] = 'useWatchlistDurability';
$dca['palettes']['__selector__'][] = 'usePublicLinkDurability';
$dca['palettes']['__selector__'][] = 'downloadLinkUseNotification';
$dca['palettes']['__selector__'][] = 'downloadLinkSendConfirmationNotification';
$dca['palettes']['__selector__'][] = 'overrideWatchlistTitle';
$dca['palettes']['__selector__'][] = 'overrideTogglerTitle';


$dca['subpalettes']['useDownloadLink']                          = 'downloadLink';
$dca['subpalettes']['useGroupWatchlist']                        = 'groupWatchlist';
$dca['subpalettes']['useWatchlistDurability']                   = 'watchlistDurability';
$dca['subpalettes']['usePublicLinkDurability']                  = 'publicLinkDurability';
$dca['subpalettes']['downloadLinkUseNotification']              = 'downloadLinkNotification,downloadLinkUseConfirmationNotification,downloadLinkFormConfigModule';
$dca['subpalettes']['overrideWatchlistTitle']                    = 'watchlistTitle';
$dca['subpalettes']['overrideTogglerTitle']                     = 'togglerTitle';



/**
 * Fields
 */
$arrFields = [
    'useMultipleWatchlist'                     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useMultipleWatchlist'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'useDownloadLink'                          => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useDownloadLink'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'downloadLink'                             => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['downloadLink'],
        'exclude'   => true,
        'inputType' => 'pageTree',
        'eval'      => ['fieldType' => 'radio', 'tl_class' => 'clr'],
        'sql'       => "blob NULL",
    ],
    'downloadLinkUseNotification'              => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['downloadLinkUseNotification'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'clr', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'downloadLinkUseConfirmationNotification' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['downloadLinkUseConfirmationNotification'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'clr'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'downloadLinkNotification'                 => $GLOBALS['TL_DCA']['tl_submission_archive']['fields']['nc_submission'],
    'downloadLinkFormConfigModule'             => [
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['downloadLinkFormConfigModule'],
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['huh.watchlist.data_container.module_container', 'getFormConfigModules'],
        'eval'             => ['includeBlankOption' => true, 'tl_class' => 'clr w50'],
        'sql'              => "varchar(64) NOT NULL default ''",
    ],
    'useGroupWatchlist'                        => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useGroupWatchlist'],
        'exclude'   => true,
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'groupWatchlist'                           => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['groupWatchlist'],
        'exclude'    => true,
        'inputType'  => 'checkbox',
        'foreignKey' => 'tl_member_group.name',
        'eval'       => ['mandatory' => true, 'multiple' => true],
        'sql'        => "blob NULL",
        'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
    ],
    'useWatchlistDurability'                   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useWatchlistDurability'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'watchlistDurability'                      => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['watchlistDurability'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
        'sql'       => "varchar(8) NOT NULL default ''",
    ],
    'usePublicLinkDurability'                  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['usePublicLinkDurability'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'useGlobalDownloadAllAction'               => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useGlobalDownloadAllAction'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'publicLinkDurability'                     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['publicLinkDurability'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
        'sql'       => "varchar(8) NOT NULL default ''",
    ],
    'watchlistItemFile'                        => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['watchlistItemFile'],
        'options_callback' => ['huh.watchlist.choice.watchlist_file', 'getChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50 clr',
            'notOverridable'     => true,
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ],
    'watchlistItemEntity'                      => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['watchlistItemEntity'],
        'options_callback' => ['huh.watchlist.choice.watchlist_entity', 'getChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50',
            'notOverridable'     => true,
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ],
    'downloadItemFile'                         => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['downloadItemFile'],
        'options_callback' => ['huh.watchlist.choice.download_file', 'getChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50 clr',
            'notOverridable'     => true,
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ],
    'downloadItemEntity'                       => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['downloadItemEntity'],
        'options_callback' => ['huh.watchlist.choice.download_entity', 'getChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50',
            'notOverridable'     => true,
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ],
    'watchlistWindowTemplate'                       => [
        'inputType'        => 'select',
        'label'            => &$GLOBALS['TL_LANG']['tl_module']['watchlistWindowTemplate'],
        'options_callback' => ['huh.watchlist.choice.watchlist_window_template', 'getCachedChoices'],
        'eval'             => [
            'chosen'             => true,
            'includeBlankOption' => true,
            'mandatory'          => true,
            'tl_class'           => 'w50',
            'notOverridable'     => true,
        ],
        'exclude'          => true,
        'sql'              => "varchar(128) NOT NULL default 'default'",
    ],
    'disableDownloadAll' =>  [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['disableDownloadAll'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'overrideWatchlistTitle' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['overrideWatchlistTitle'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'clr w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'watchlistTitle' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['watchlistTitle'],
        'exclude'   => true,
        'inputType' => 'select',
        'options_callback' => function (\DataContainer $dc) {
            return \Contao\System::getContainer()->get('huh.watchlist.choice.watchlist_label')->getCachedChoices('huh.watchlist.watchlist_label');
        },
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
        'sql'       => "varchar(64) NOT NULL default ''",
    ],
    'overrideTogglerTitle' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['overrideTogglerTitle'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'clr w50', 'submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'togglerTitle' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['togglerTitle'],
        'exclude'   => true,
        'inputType' => 'select',
        'options_callback' => function (\DataContainer $dc) {
            return \Contao\System::getContainer()->get('huh.watchlist.choice.watchlist_label')->getCachedChoices('huh.watchlist.watchlist_label');
        },
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
        'sql'       => "varchar(64) NOT NULL default ''",
    ]
];

$dca['fields']['downloadLinkNotification']['label'] = &$GLOBALS['TL_LANG']['tl_module']['downloadLinkNotification'];

$dc['fields'] = array_merge($dc['fields'], $arrFields);