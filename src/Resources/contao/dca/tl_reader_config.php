<?php

\Contao\Controller::loadDataContainer('tl_module');
\Contao\System::loadLanguageFile('tl_module');

$dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

$dca['fields']['watchlist_config'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['watchlist_config'],
    'inputType'        => 'select',
    'options_callback' => function () {
        if (null === ($modules = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_module', ['tl_module.type=?'], [\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist::MODULE_WATCHLIST]))) {
            return [];
        }

        $options = [];
        while ($modules->next()) {
            $options[$modules->id] = $modules->name;
        }

        return $options;
    },
    'eval'             => [
        'includeBlankOption' => true,
        'tl_class'           => 'w50',
    ],
    'sql'              => "varchar(255) NOT NULL default ''",
];

$dca['palettes']['default'] = str_replace('headTags;', 'headTags;{watchlist_legend},watchlist_config;', $dca['palettes']['default']);
