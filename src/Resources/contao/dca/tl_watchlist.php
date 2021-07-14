<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_watchlist'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['tl_watchlist_item'],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'hash' => 'unique',
                'uuid' => 'unique',
            ],
        ],
    ],
    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'sessionID' => [
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'hash' => [
            'sql' => 'varchar(40) NULL',
        ],
        'ip' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'published' => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'start' => [
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'uuid' => [
            'sql' => "varchar(36) NOT NULL default ''",
        ],
        'startShare' => [
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'activation' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];
