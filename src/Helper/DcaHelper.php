<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\Helper;


use Contao\Controller;

class DcaHelper
{
    /**
     * Add all necessary fields to the dca.
     *
     * @param string $table Dca table
     * @param string $insertAfter A dca palette string (for example a field) to add watchlist field after. Leave empty, if you want to add the field by yourself (addAddToWatchlistButton).
     * @param string $palette The palette where to add watchlist fields. By default the default palette is used.
     */
    public static function addDcaFields(string $table, string $insertAfter = '', string $palette = 'default'): void
    {
        Controller::loadDataContainer($table);
        if (!isset($GLOBALS['TL_DCA'][$table]))
        {
            return;
        }
        $dca = &$GLOBALS['TL_DCA'][$table];
        $dca['palettes']['__selector__'][] = 'addAddToWatchlistButton';
        $dca['subpalettes']['addAddToWatchlistButton'] = 'watchlistConfiguration';

        $dca['fields']['addAddToWatchlistButton'] = [
            'label'     => &$GLOBALS['TL_LANG'][$table]['addAddToWatchlistButton'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ];
        $dca['fields']['watchlistConfiguration'] = [
            'label'            => &$GLOBALS['TL_LANG'][$table]['watchlistConfiguration'],
            'inputType'        => 'select',
            'options_callback' => ['huh.watchlist.data_container.module_container', 'getWatchlistModules'],
            'eval'             => [
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'mandatory'          => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ];

        if (!empty($insertAfter))
        {
            $dca['palettes'][$palette] = str_replace($insertAfter, $insertAfter.',addAddToWatchlistButton', $dca['palettes'][$palette]);
        }
    }


}