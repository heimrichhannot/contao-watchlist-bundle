<?php

$lang = &$GLOBALS['TL_LANG']['tl_watchlist_config'];

/**
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';
$lang['title'][0]  = 'Titel';
$lang['title'][1]  = 'Geben Sie hier bitte den Titel ein.';

$lang['watchlistFrontendFramework'] = ['Watchlist Frontend Framework', 'Wählen Sie hier das Framework, welche für die Darstellung genutzt werden soll.'];
$lang['useMultipleWatchlist']       = ['Mehrere Merklisten benutzen', 'Mehrere Merklisten benutzen und verwalten'];
$lang['useGroupWatchlist']          =
    ['Mitgliedergruppe zuweisen', 'Mehrere Mitglieder der gleichen Gruppe können die selben Merklisten sehen und verwalten.'];
$lang['groupWatchlist']             = ['Erlaubte Mitgliedergruppe', 'Diese Gruppen können die Merkliste verwalten und sehen.'];
$lang['useWatchlistDurability']     = ['Lebenszeit für Watchlist definieren', 'Wählen Sie diese Option, wenn die Watchlist eine begrenzte Lebenszeit erhalten soll. Die Lebensdauer wird nur für den Fall angewendet, dass "Mehrere Merklisten benutzen" aktiviert ist.'];
$lang['watchlistDurability']        = ['Lebenszeit', 'Tragen Sie hier die Lebenszeit in Tagen ein.'];
$lang['useGlobalDownloadAllAction'] = ['Globale Download-All Aktion', 'Wählen Sie diese Option, wenn der Download-All Button auch außerhalb des Merklisten-Fenster dargestellt werden soll.'];
$lang['disableDownloadAll']         = ['"Merkliste herunterladen" deaktivieren ', 'Wählen Sie diese Option, wenn der "Merkliste herunterladen"-Button im Merklisten-Modal nicht dargestellt werden soll.'];
$lang['overrideWatchlistTitle']     = ['Merklistennamen vorgeben', 'Wählen Sie diese Option, wenn der Name der Merkliste vorgegeben werden soll.'];
$lang['watchlistTitle']             = ['Merklistenname', 'Tragen Sie hier den Namen der Merkliste ein.'];
$lang['overrideTogglerTitle']       = ['Titel des Merklisten-Button vorgeben', 'Wählen Sie diese Option, wenn der Titel des Merklisten-Button vorgegeben werden soll.'];
$lang['togglerTitle']               = ['Titel des Merklisten-Button', 'Tragen Sie hier den Titel des Merklisten-Button ein.'];

$lang['watchlistItemFile']   = ['File-Item-Klasse', 'Die Item-Klasse, welche ein Watchlist-Item repräsentiert'];
$lang['watchlistItemEntity'] = ['Entity-Item-Klasse',];
$lang['downloadItemFile']    = ['File-Download-Item-Klasse'];
$lang['downloadItemEntity']  = ['Entity-Download-Item-Klasse'];

$lang['useDownloadLink']                         = ['Downloadlink anzeigen', 'Erstellt einen Link zu einer Weiterleitungsseite, welche alle Elemente einer Merkliste zum Download zur Verfügung stellt.'];
$lang['downloadLink']                            = ['Weiterleitungsseite für den Downloadlink', 'Weiterleitungsseite, welche alle Elemente einer Merkliste zum Download zur Verfügung stellt.'];
$lang['downloadLinkUseNotification']             = ['Downloadlink per Benachrichtigung versenden', 'Wählen Sie diese Option, wenn Sie den Downloadlink per Benachrichtigung (üblicherweise E-Mail) versenden möchten.'];
$lang['downloadLinkNotification']                = ['Downloadlink-Benachrichtigung', 'Wählen Sie hier die Nachricht aus, über die der Downloadlink verschickt werden soll.'];
$lang['downloadLinkUseConfirmationNotification'] = ['Bestätigungsbenachrichtigung versenden', 'Wählen Sie diese Option, wenn der Nutzer vor erhalt des Downloadlinks eine Bestätigungsemail erhalten soll.'];
$lang['downloadLinkFormConfigModule']            = ['Formular-Konfiguration', 'Wählen Sie hier das Module aus, welches die Konfiguration für das Formular vorgibt. ACHTUNG: Wird hier keine Konfiguration ausgewählt, wird versucht die Nutzerdaten für den Versandt des Downloadlinks vom Frontend-Nutzer zu beziehen.'];
$lang['skipItemsForDownloadList']                = ['Items von der Downloadliste einschränken', 'Wählen Sie diese Option, wenn Sie einschränken möchten, welche Items in der Downloadliste aufgeführt werden sollen. Diese Items können dennoch zur Merkliste hinzugefügt werden.'];
$lang['skipItemsForDownloadListConfig']          = ['Bedingungen', ''];
$lang['skipItemsForDownloadListConfig']['field'] = ['Feld', ''];
$lang['skipItemsForDownloadListConfig']['operator'] = ['Operator', ''];
$lang['skipItemsForDownloadListConfig']['value'] = ['Wert', ''];
$lang['skipItemsDataContainer'] = ['DataContainer', ''];
$lang['addDetails'] = ['Detail-Weiterleitung hinzufügen', 'Wählen Sie diese Option, wenn den Elementen in der Merkliste eine Verlinkung zu einer Detailseite hinzugefügt werden soll.'];
$lang['jumpToDetails'] = ['Weiterleitungsseite', ''];
$lang['alias'] = ['Alias', 'Tragen Sie hier die Feldbezeichnung ein anhand der die Entität auf der Detailsseite gefunden werden soll.'];



/**
 * Legends
 */
$lang['general_legend']             = 'Allgemeine Einstellungen';
$lang['display_legend']             = 'Darstellung';
$lang['additional_settings_legend'] = 'Erweiterte Einstellungen';
$lang['item_legend']                = 'Items';
$lang['download_legend']            = 'Download';

/**
 * Buttons
 */
$lang['new'][0]    = 'Neues Merklistenkonfiguration';
$lang['new'][1]    = 'Merklistenkonfiguration erstellen';
$lang['edit'][0]   = 'Merklistenkonfiguration bearbeiten';
$lang['edit'][1]   = 'Merklistenkonfiguration ID %s bearbeiten';
$lang['copy'][0]   = 'Merklistenkonfiguration duplizieren';
$lang['copy'][1]   = 'Merklistenkonfiguration ID %s duplizieren';
$lang['delete'][0] = 'Merklistenkonfiguration löschen';
$lang['delete'][1] = 'Merklistenkonfiguration ID %s löschen';
$lang['show'][0]   = 'Merklistenkonfiguration Details';
$lang['show'][1]   = 'Merklistenkonfiguration-Details ID %s anzeigen';

/**
 * Frontend Frameworks
 */

$lang['FRONTENDFRAMEWORK']['base'] = "Basis";
$lang['FRONTENDFRAMEWORK']['bs4']  = "Bootstrap 4";
