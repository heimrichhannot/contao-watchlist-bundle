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
$dc['palettes'][\HeimrichHannot\WatchlistBundle\FrontendModule\ModuleWatchlist::MODULE_WATCHLIST] =
    '{title_legend},name,headline,type;
    {watchlist_legend},watchlistConfig;
    {template_legend:hide},customTpl;
    {download_legend}, downloadLinkUseNotification;
    {protected_legend:hide},protected;
    {misc_legend},imgSize;
    {expert_legend:hide},guests,cssID,space';

$dc['palettes'][\HeimrichHannot\WatchlistBundle\FrontendModule\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST] =
    '{title_legend},name,headline,type;{template_legend:hide},customTpl;{additionalSettingsLegend},listConfig,usePublicLinkDurability;{protected_legend:hide},protected;{misc_legend},imgSize;{expert_legend:hide},guests,cssID,space';

/**
 * Subpalettes
 */
$dca['palettes']['__selector__'][] = 'usePublicLinkDurability';
$dca['palettes']['__selector__'][] = 'downloadLinkUseNotification';
$dca['palettes']['__selector__'][] = 'downloadLinkSendConfirmationNotification';


$dca['subpalettes']['usePublicLinkDurability']                  = 'publicLinkDurability,useDownloadAllAction';
$dca['subpalettes']['downloadLinkUseNotification']              = 'downloadLinkNotification,downloadLinkUseConfirmationNotification,downloadLinkFormConfigModule';


/**
 * Fields
 */
$arrFields = [
    'watchlistConfig' => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['watchlistConfig'],
        'exclude'    => true,
        'filter'     => true,
        'inputType'  => 'select',
        'foreignKey' => 'tl_watchlist_config.title',
        'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        'eval'       => ['tl_class' => 'w50 clr wizard', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
        'wizard'           => [
            ['huh.watchlist.data_container.module_container', 'editWatchlistWizard'],
        ],
        'sql'        => "int(10) unsigned NOT NULL default '0'"
    ],
    'usePublicLinkDurability'                  => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['usePublicLinkDurability'],
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
    'useDownloadAllAction'                     => [
        'label'     => &$GLOBALS['TL_LANG']['tl_module']['useDownloadAllAction'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
];

$dca['fields']['downloadLinkNotification']['label'] = &$GLOBALS['TL_LANG']['tl_module']['downloadLinkNotification'];

$dc['fields'] = array_merge($dc['fields'], $arrFields);