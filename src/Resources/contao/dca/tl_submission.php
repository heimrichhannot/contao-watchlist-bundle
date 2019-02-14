<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 14.02.19
 * Time: 10:38
 */

$dca = &$GLOBALS['TL_DCA']['tl_submission'];

$dca['palettes']['default'] .= 'module,watchlistId';

$dca['fields']['module'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_submission']['module'],
    'exclude' => true,
    'inputType' => 'hidden',
    'load_callback' => [['huh.watchlist.data_container.module_container', 'getModuleId']],
    'sql' => "int(3) unsigned NOT NULL default '0'",
];

$dca['fields']['watchlistId'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_submission']['module'],
    'exclude' => true,
    'inputType' => 'hidden',
    'load_callback' => [['huh.watchlist.data_container.module_container', 'getWatchlistId']],
    'sql' => "int(3) unsigned NOT NULL default '0'",
];

//$dca['privacyJumpTo']