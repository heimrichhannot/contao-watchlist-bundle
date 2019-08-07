<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$lang = &$GLOBALS['TL_LANG']['tl_list_config_element'];

$lang['overrideWatchlistConfig'] = ['Override watchlist configuration', 'Override the default watchlist configuration for this element.'];
$lang['watchlistConfig']         = ['Watchlist configuration', 'Choose the watchlist configuration for this element'];
$lang['fileField']               = ['File field', 'Choose the field which reference the download file.'];
$lang['titleField']              = ['Title field', 'Choose the field which contains the download file title.'];

$lang['reference'][\HeimrichHannot\WatchlistBundle\ConfigElement\WatchlistConfigElementType::TYPE] = 'Watchlist ("Add to watchlist" button)';