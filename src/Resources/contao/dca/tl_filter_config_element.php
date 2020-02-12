<?php

$dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];

$dca['palettes'][\HeimrichHannot\WatchlistBundle\Filter\Type\WatchlistDownloadType::TYPE] = '{general_legend},title,type;{config_legend},watchlistConfig;{publish_legend},published;';

$dca['fields']['watchlistConfig'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_filter_config_element']['watchlistConfig'],
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
];