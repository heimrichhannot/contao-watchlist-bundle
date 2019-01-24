<?php


$lang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$lang['useMultipleWatchlist'] = ['Mehrere Merklisten benutzen', 'Mehrere Merklisten benutzen und verwalten'];
$lang['useDownloadLink']      = [
    'Downloadlink anzeigen',
    'Erstellt einen Link zu einer Weiterleitungsseite, welche alle Elemente einer Merkliste zum Download zur Verfügung stellt.'
];
$lang['downloadLink']         =
    ['Weiterleitungsseite für den Downloadlink', 'Weiterleitungsseite, welche alle Elemente einer Merkliste zum Download zur Verfügung stellt.'];
$lang['useGroupWatchlist']    =
    ['Mitgliedergruppe zuweisen', 'Mehrere Mitglieder der gleichen Gruppe können die selben Merklisten sehen und verwalten.'];
$lang['groupWatchlist']       = ['Erlaubte Mitgliedergruppe', 'Diese Gruppen können die Merkliste verwalten und sehen.'];


$lang['useWatchlistDurability'] = ['Lebenszeit für Watchlist definieren', 'Wählen Sie diese Option, wenn die Watchlist eine begrenzte Lebenszeit erhalten soll. Die Lebensdauer wird nur für den Fall angewendet, dass "Mehrere Merklisten benutzen" aktiviert ist.'];
$lang['watchlistDurability']    = ['Lebenszeit', 'Tragen Sie hier die Lebenszeit in Tagen ein.'];

$lang['watchlistItemFile']      = ['Listen-Klasse für File-Items', 'Wählen Sie hier eine Klasse für die Darstellung eines Items vom Typ `file` in der Liste aus.'];
$lang['watchlistItemEntity']    = ['Listen-Klasse für Entity-Items', 'Wählen Sie hier eine Klasse für die Darstellung eines Items vom Typ `entity` in der Liste aus.'];
$lang['downloadItemFile']       = ['Download-Klasse für File-Items', 'Wählen Sie hier eine Klasse für die Generierung des Downloads vom Typ `file` aus.'];
$lang['downloadItemEntity']     = ['Download-Klasse für Entity-Items', 'Wählen Sie hier eine Klasse für die Generierung des Downloads vom Typ `entity` aus.'];

$lang['useWatchlistDurability'] = ['Lebensdauer der Merkliste definieren','Wählen Sie diese Option, wenn die Lebensdauer der Merkliste begrenzt werden soll.'];
$lang['watchlistDurability']    = ['Lebensdauer','Tragen Sie hier die Lebensdauer ein.'];
$lang['groupWatchlist']         = ['Nutzergruppen'];

$lang['useGlobalDownloadAllAction'] = ['Globale Download-All Action', 'Wählen Sie diese Option, wenn der Download-All Button auch außerhalb des Merklisten-Modal dargestellt werden soll.'];

$lang['watchlistItemFile']      = ['File-Item-Klasse'];
$lang['watchlistItemEntity']    = ['Entity-Item-Klasse'];

$lang['downloadItemFile']       = ['File-Download-Item-Klasse'];
$lang['downloadItemEntity']     = ['Entity-Download-Item-Klasse'];

$lang['usePublicLinkDurability']  = ['Verfallzeit hinzufügen', 'Wählen Sie diese Option, wenn der geteilte Link nur für eine eingeschränkte Zeit gültig sein soll.'];
$lang['publicLinkDurability']     = ['Verfallzeit', 'Tragen Sie hier die Zeit in Sekunden ein für die der Link gültig ist.'];

$lang['downloadLinkUseNotification'] = ['Downloadlink per Email versenden', 'Wählen Sie diese Option, wenn Sie den Downloadlink per Benachrichtigung versenden möchten.'];
$lang['downloadLinkNotification'] = ['Downloadlink-Benachrichtigung', 'Wählen Sie hier die Nachricht aus, über die der Downloadlink verschickt werden soll'];

$lang['disableDownloadAll'] = ['"Merkliste herunterladen" deaktivieren ', 'Wählen Sie diese Option, wenn der "Merkliste herunterladen"-Button im Merklisten-Modal nicht dargestellt werden soll.'];

$lang['overrideWatchlistTitle'] = ['Merklistennamen vorgeben', 'Wählen Sie diese Option, wenn der Name der Merkliste vorgegeben werden soll.'];
$lang['watchlistTitle'] = ['Merklistenname', 'Tragen Sie hier den Namen der Merkliste ein.'];

$lang['overrideTogglerTitle'] = ['Titel des Merklisten-Button vorgeben', 'Wählen Sie diese Option, wenn der Titel des Merklisten-Button vorgegeben werden soll.'];
$lang['togglerTitle'] = ['Titel des Merklisten-Button', 'Tragen Sie hier den Titel des Merklisten-Button ein.'];

$lang['downloadLinkUseConfirmationNotification'] = ['Bestätigungsbenachrichtigung versenden', 'Wählen Sie diese Option, wenn der Nutzer vor erhalt des Downloadlinks eine Bestätigungsemail erhalten soll.'];

$lang['downloadLinkFormConfigModule'] = ['Formular-Konfiguration', 'Wählen Sie hier das Module aus, welches die Konfiguration für das Formular vorgibt. ACHTUNG: Wird hier keine Konfiguration ausgewählt, wird versucht die Nutzerdaten für den Versandt des Downloadlinks vom Frontend-Nutzer zu beziehen.'];


/**
 * Legends
 */
$lang['additionalSettingsLegend'] = 'Erweiterte Einstellungen';


/**
 * Front-end modules
 */
$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlist::MODULE_WATCHLIST]                           =
    ['Merkliste', 'Füge Inhaltselement zur Merkliste hinzu und lade diese auf einmal herunter.'];
$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Module\ModuleWatchlistDownloadList::MODULE_WATCHLIST_DOWNLOAD_LIST] =
    ['Merkliste - Download', 'Auflistung der Elemente einer Merkliste mit Download-Funktion.'];