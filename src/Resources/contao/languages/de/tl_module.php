<?php


$lang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$lang['watchlistConfig'] = ['Merklisten-Konfiguration', 'Wählen Sie hier die Merklisten-Konfiguration für das Modul aus.'];


$lang['useMultipleWatchlist'] = ['Mehrere Merklisten benutzen', 'Mehrere Merklisten benutzen und verwalten'];
$lang['useGroupWatchlist']    =
    ['Mitgliedergruppe zuweisen', 'Mehrere Mitglieder der gleichen Gruppe können die selben Merklisten sehen und verwalten.'];
$lang['groupWatchlist']       = ['Erlaubte Mitgliedergruppe', 'Diese Gruppen können die Merkliste verwalten und sehen.'];


$lang['groupWatchlist']         = ['Nutzergruppen'];


$lang['usePublicLinkDurability']  = ['Verfallzeit hinzufügen', 'Wählen Sie diese Option, wenn der geteilte Link nur für eine eingeschränkte Zeit gültig sein soll.'];
$lang['publicLinkDurability']     = ['Verfallzeit', 'Tragen Sie hier die Zeit in Sekunden ein für die der Link gültig ist.'];


/**
 * Legends
 */
$lang['watchlist_legend'] = 'Merkliste';
$lang['additionalSettingsLegend'] = 'Erweiterte Einstellungen';


/**
 * Front-end modules
 */
$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist::MODULE_WATCHLIST]                           =
    ['Merkliste', 'Füge Inhaltselement zur Merkliste hinzu und lade diese auf einmal herunter.'];
$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST] =
    ['Merkliste - Download', 'Auflistung der Elemente einer Merkliste mit Download-Funktion.'];