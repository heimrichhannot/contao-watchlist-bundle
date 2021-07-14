<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_list_config_element'];

$lang['overrideWatchlistConfig'][0] = 'Merklisten-Konfiguration überschreiben';
$lang['overrideWatchlistConfig'][1] = 'Eine individuelle Merklisten-Konfiguration für dieses Element verwenden.';
$lang['watchlistConfig'][0] = 'Merklisten-Konfiguration';
$lang['watchlistConfig'][1] = 'Wählen Sie hier die Merklisten-Konfiguration für das Element aus.';
$lang['fileField'][0] = 'Datei-Feld';
$lang['fileField'][1] = 'Wählen Sie hier das Feld aus, welches den Download referenziert.';
$lang['titleField'][0] = 'Titel-Feld';
$lang['titleField'][1] = 'Wählen Sie hier das Feld aus, welches den Titel für den Download enthält.';
$lang['watchlistType'][0] = 'Typ';
$lang['watchlistType'][1] = 'Wählen Sie hier den Typ des hinzuzufügenden Elementes.';
$lang['watchlistType'][\HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE] = 'Datei';
$lang['watchlistType'][\HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::WATCHLIST_ITEM_TYPE_ENTITY] = 'Entität';

$lang['reference'][\HeimrichHannot\WatchlistBundle\ConfigElementType\WatchlistConfigElementListType::TYPE] = 'Watchlist ("Zur Watchlist hinzufügen"-Button)';
