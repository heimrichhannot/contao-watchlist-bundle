<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_reader_config'];

/*
 * Palettes
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField('actAsWatchlistShareTarget', 'misc_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_reader_config');

/**
 * Fields.
 */
$fields = [
    'actAsWatchlistShareTarget' => [
        'label' => &$GLOBALS['TL_LANG']['tl_reader_config']['actAsWatchlistShareTarget'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'w50'],
        'sql' => "char(1) NOT NULL default ''",
    ],
];

$dca['fields'] = array_merge(is_array($dca['fields']) ? $dca['fields'] : [], $fields);
