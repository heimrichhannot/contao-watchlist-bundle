<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$lang = &$GLOBALS['TL_LANG']['tl_reader_config_element'];

$lang['overrideWatchlistConfig'][0]                                                                          = 'Merklisten-Konfiguration überschreiben';
$lang['overrideWatchlistConfig'][1]                                                                          = 'Eine individuelle Merklisten-Konfiguration für dieses Element verwenden.';
$lang['watchlistConfig'][0]                                                                                  = 'Merklisten-Konfiguration';
$lang['watchlistConfig'][1]                                                                                  = 'Wählen Sie hier die Merklisten-Konfiguration für das Element aus.';
$lang['fileField'][0]                                                                                        = 'Datei-Feld';
$lang['fileField'][1]                                                                                        = 'Wählen Sie hier das Feld aus, welches den Download referenziert.';
$lang['titleField'][0]                                                                                       = 'Titel-Feld';
$lang['titleField'][1]                                                                                       = 'Wählen Sie hier das Feld aus, welches den Titel für den Download enthält.';
$lang['watchlistType'][0]                                                                                    = 'Typ';
$lang['watchlistType'][1]                                                                                    = 'Wählen Sie hier den Typ des hinzuzufügenden Elementes.';
$lang['watchlistType'][\HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE]   = 'Datei';
$lang['watchlistType'][\HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel::WATCHLIST_ITEM_TYPE_ENTITY] = 'Entität';

$lang['reference'][\HeimrichHannot\WatchlistBundle\ConfigElementType\WatchlistConfigElementReaderType::TYPE] = 'Watchlist ("Zur Watchlist hinzufügen"-Button)';