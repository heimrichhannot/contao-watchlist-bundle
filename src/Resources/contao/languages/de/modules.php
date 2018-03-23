<?php


$arrLang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$arrLang['useMultipleWatchlist'] = ['Mehrere Merklisten benutzen', 'Mehrere Merklisten benutzen und verwalten'];
$arrLang['useDownloadLink']      = [
    'Downloadlink anzeigen',
    'Erstellt einen Link zu einer Weiterleitungsseite, welche alle Elemente einer Merkliste zum Download zur Verfügung stellt.'
];
$arrLang['downloadLink']         =
    ['Weiterleitungsseite für den Downloadlink', 'Weiterleitungsseite, welche alle Elemente einer Merkliste zum Download zur Verfügung stellt.'];
$arrLang['useGroupWatchlist']    =
    ['Mitgliedergruppe zuweisen', 'Mehrere Mitglieder der gleichen Gruppe können die selben Merklisten sehen und verwalten.'];
$arrLang['groupWatchlist']       = ['Erlaubte Mitgliedergruppe', 'Diese Gruppen können die Merkliste verwalten und sehen.'];

$arrLang['fileFieldEntity']      = [
    'Erlaubte Entität-Felder für Datei',
    'Tragen Sie hier die Felder ein, in der an Entitäten nach Dateien gesucht werden soll. Wird bei einer Entität dieses Feld gefunden, wird dieser Wert in als Watchlist-Item eingetragen.'
];
$arrLang['fileFieldChildEntity'] = [
    'Erlaubte Kind-Entität-Felder für Datei',
    'Tragen Sie hier die Felder ein, in der an Kind-Entitäten der aktuellen Entität nach Dateien gesucht werden soll. Wird bei einer Entität dieses Feld gefunden, wird dieser Wert in als Watchlist-Item eingetragen.'
];
$arrLang['fieldName'] = ['Feldname', 'Tragen Sie hier den Feldnamen nach dem an der Entität gesucht wird.'];


/**
 * Legends
 */
$arrLang['additionalSettingsLegend'] = 'Erweiterte Einstellungen';


/**
 * Front-end modules
 */

$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist::MODULE_WATCHLIST]                           =
    ['Merkliste', 'Füge Inhaltselement zur Merkliste hinzu und lade diese auf einmal herunter.'];
$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST] =
    ['Merkliste - Download', 'Auflistung der Elemente einer Merkliste mit Download-Funktion.'];