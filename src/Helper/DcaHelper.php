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
     * @param string $palettePosition A dca  string (for example a field or legend) to add watchlist field before.
     *                              Leave empty, if you want to add the field by yourself (addAddToWatchlistButton).
     *                              If a $legendName is set, the dca field will be added completly with legend.
     *                              If $legendName is empty, the dca field will be just added as field with trailing comma.
     * @param string $palette The palette where to add watchlist fields. By default the default palette is used.
     * @param string $legendName
     * @return array Returns a reference to the dca array
     */
    public static function addDcaFields(string $table, string $palettePosition = '', string $palette = 'default', string $legendName = 'watchlist_legend'): array
    {
        Controller::loadDataContainer($table);
        if (!isset($GLOBALS['TL_DCA'][$table]))
        {
            return [];
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

        static::addDcaMapping($dca, $palettePosition, $palette, $legendName);

        return $dca;
    }

    /**
     * Add an additional palette mapping for field from DcaHelper::addDcaFields()
     *
     * @param array $dca
     * @param string $palettePosition
     * @param string $palette
     * @param string $legendName
     */
    public static function addDcaMapping(array &$dca, string $palettePosition = '', string $palette = 'default', string $legendName = 'watchlist_legend')
    {
        if (!empty($palettePosition))
        {
            $insert = 'addAddToWatchlistButton,';
            if (!empty($legendName)) {
                $insert = '{'.$legendName.'},addAddToWatchlistButton;';
            }
            if (!$palette) {
                $palette = 'default';
            }
            if (isset($dca['palettes'][$palette]))
            {
                $dca['palettes'][$palette] = str_replace($palettePosition, $insert.$palettePosition, $dca['palettes'][$palette]);
            }
        }
    }


}