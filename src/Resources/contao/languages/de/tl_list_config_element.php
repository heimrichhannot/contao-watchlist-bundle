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

$lang['overrideWatchlistConfig'] = ['Merklisten-Konfiguration überschreiben', 'Eine individuelle Merklisten-Konfiguration für dieses Element verwenden.'];
$lang['watchlistConfig']         = ['Merklisten-Konfiguration', 'Wählen Sie hier die Merklisten-Konfiguration für das Element aus.'];
$lang['fileField']               = ['Datei-Feld', 'Wählen Sie hier das Feld aus, welches den Download referenziert.'];
$lang['titleField']              = ['Titel-Feld', 'Wählen Sie hier das Feld aus, welches den Titel für den Download enthält.'];

$lang['reference'][\HeimrichHannot\WatchlistBundle\ConfigElement\WatchlistConfigElementType::TYPE] = 'Watchlist ("Zur Watchlist hinzufügen"-Button)';