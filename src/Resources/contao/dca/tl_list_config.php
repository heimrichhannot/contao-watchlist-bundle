<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

\Contao\Controller::loadDataContainer('tl_module');
\Contao\System::loadLanguageFile('tl_module');

$dca = &$GLOBALS['TL_DCA']['tl_list_config'];

$dca['fields']['watchlistConfig'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_module']['watchlistConfig'],
        'exclude' => true,
        'filter' => true,
        'inputType' => 'select',
        'foreignKey' => 'tl_watchlist_config.title',
        'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        'eval' => ['tl_class' => 'w50 clr wizard', 'includeBlankOption' => true, 'chosen' => true],
        'wizard' => [
            ['huh.watchlist.data_container.module_container', 'editWatchlistWizard'],
        ],
        'sql' => "int(10) unsigned NOT NULL default '0'",
];

$dca['palettes']['default'] = str_replace('addShare;', 'addShare;{watchlist_legend},watchlistConfig;', $dca['palettes']['default']);
