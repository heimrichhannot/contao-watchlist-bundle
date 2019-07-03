<?php

$GLOBALS['TL_DCA']['tl_watchlist_config'] = [
    'config'   => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => false,
        'onload_callback' => [
            ['huh.watchlist.data_container.watchlist_config', 'onLoadCallback']
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary'            ]
        ]
    ],
    'list'     => [
        'label' => [
            'fields' => ['title', 'watchlistFrontendFramework'],
            'format' => '%s <span style="color:#999;">(%s)</span>'
        ],
        'sorting'           => [
            'mode'                  => 1,
            'fields'                => ['title'],
            'headerFields'          => [''],
            'panelLayout'           => 'filter;sort,search,limit'
        ],
        'global_operations' => [
            'all'    => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations' => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_watchlist_config']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_watchlist_config']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
        ]
    ],
    'palettes' => [
        '__selector__' => [
            'published',
            'useGroupWatchlist',
            'useWatchlistDurability',
            'overrideWatchlistTitle',
            'overrideTogglerTitle',
            'useDownloadLink',
            'downloadLinkUseNotification',
        ],
        'default' => '{general_legend},title;'
            .'{display_legend},watchlistFrontendFramework;'
            .'{additional_settings_legend},useMultipleWatchlist,useGroupWatchlist,useWatchlistDurability,useGlobalDownloadAllAction,disableDownloadAll,overrideWatchlistTitle,overrideTogglerTitle;'
            .'{item_legend},watchlistItemFile,watchlistItemEntity,downloadItemFile,downloadItemEntity;'
            .'{download_legend},useDownloadLink;'
    ],
    'subpalettes' => [
        'published'    => 'start,stop',
        'useGroupWatchlist' => 'groupWatchlist',
        'useWatchlistDurability' => 'watchlistDurability',
        'overrideWatchlistTitle' => 'watchlistTitle',
        'overrideTogglerTitle' => 'togglerTitle',
        'useDownloadLink' => 'downloadLink',
        'downloadLinkUseNotification' => 'downloadLinkNotification,downloadLinkUseConfirmationNotification,downloadLinkFormConfigModule',
    ],
    'fields'   => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_watchlist_config']['tstamp'],
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded' => [
            'label'                   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting'                 => true,
            'flag'                    => 6,
            'eval'                    => ['rgxp'=>'datim', 'doNotCopy' => true],
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_watchlist_config']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => ['mandatory' => true, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'watchlistFrontendFramework'                       => [
            'inputType'        => 'select',
            'label'            => &$GLOBALS['TL_LANG']['tl_watchlist_config']['watchlistFrontendFramework'],
            'options_callback' => ['huh.watchlist.data_container.module_container', 'getWatchlistFrontendFrameworks'],
            'eval'             => [
                'mandatory' => true,
                'tl_class'           => 'w50',
            ],
            'exclude'          => true,
            'sql'              => "varchar(32) NOT NULL default ''",
        ],
        'useMultipleWatchlist'                     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['useMultipleWatchlist'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'useGroupWatchlist'                        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['useGroupWatchlist'],
            'exclude'   => true,
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'groupWatchlist'                           => [
            'label'      => &$GLOBALS['TL_LANG']['tl_watchlist_config']['groupWatchlist'],
            'exclude'    => true,
            'inputType'  => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval'       => ['mandatory' => true, 'multiple' => true],
            'sql'        => "blob NULL",
            'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'useWatchlistDurability'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['useWatchlistDurability'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'watchlistDurability'                      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['watchlistDurability'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(8) NOT NULL default ''",
        ],
        'useGlobalDownloadAllAction'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['useGlobalDownloadAllAction'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'watchlistItemFile'                        => [
            'inputType'        => 'select',
            'label'            => &$GLOBALS['TL_LANG']['tl_watchlist_config']['watchlistItemFile'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_watchlist_config']['watchlistItemEntity'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_watchlist_config']['downloadItemFile'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_watchlist_config']['downloadItemEntity'],
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
        'disableDownloadAll' =>  [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['disableDownloadAll'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'overrideWatchlistTitle' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['overrideWatchlistTitle'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'clr w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'watchlistTitle' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['watchlistTitle'],
            'exclude'   => true,
            'inputType' => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.watchlist.choice.watchlist_label')->getCachedChoices('huh.watchlist.watchlist_label');
            },
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'overrideTogglerTitle' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['overrideTogglerTitle'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'clr w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'togglerTitle' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['togglerTitle'],
            'exclude'   => true,
            'inputType' => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.watchlist.choice.watchlist_label')->getCachedChoices('huh.watchlist.watchlist_label');
            },
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'useDownloadLink'                          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['useDownloadLink'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'downloadLink'                             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['downloadLink'],
            'exclude'   => true,
            'inputType' => 'pageTree',
            'eval'      => ['fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql'       => "blob NULL",
        ],
        'downloadLinkUseNotification'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['downloadLinkUseNotification'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'clr', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'downloadLinkUseConfirmationNotification' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_watchlist_config']['downloadLinkUseConfirmationNotification'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'downloadLinkNotification'                 => [
            'label'            => &$GLOBALS['TL_LANG']['tl_submission_archive']['nc_submission'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['HeimrichHannot\Submissions\Submissions', 'getNotificationsAsOptions'],
            'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'downloadLinkFormConfigModule'             => [
            'label'            => &$GLOBALS['TL_LANG']['tl_watchlist_config']['downloadLinkFormConfigModule'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['huh.watchlist.data_container.module_container', 'getFormConfigModules'],
            'eval'             => ['includeBlankOption' => true, 'tl_class' => 'clr w50'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
    ],
];