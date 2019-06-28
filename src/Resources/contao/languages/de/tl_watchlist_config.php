<?php

$lang = &$GLOBALS['TL_LANG']['tl_watchlist_config'];

/**
 * Fields
 */
$lang['tstamp'][0]       = 'Änderungsdatum';
$lang['title'][0]        = 'Titel';
$lang['title'][1]        = 'Geben Sie hier bitte den Titel ein.';
$lang['useDownloadLink'] = ['Downloadlink anzeigen', 'Erstellt einen Link zu einer Weiterleitungsseite, welche alle Elemente einer Merkliste zum Download zur Verfügung stellt.'];
$lang['downloadLink']    = ['Weiterleitungsseite für den Downloadlink', 'Weiterleitungsseite, welche alle Elemente einer Merkliste zum Download zur Verfügung stellt.'];
$lang['downloadLinkUseNotification'] = ['Downloadlink per Benachrichtigung versenden', 'Wählen Sie diese Option, wenn Sie den Downloadlink per Benachrichtigung (üblicherweise E-Mail) versenden möchten.'];
$lang['downloadLinkNotification'] = ['Downloadlink-Benachrichtigung', 'Wählen Sie hier die Nachricht aus, über die der Downloadlink verschickt werden soll.'];
$lang['downloadLinkUseConfirmationNotification'] = ['Bestätigungsbenachrichtigung versenden', 'Wählen Sie diese Option, wenn der Nutzer vor erhalt des Downloadlinks eine Bestätigungsemail erhalten soll.'];
$lang['downloadLinkFormConfigModule'] = ['Formular-Konfiguration', 'Wählen Sie hier das Module aus, welches die Konfiguration für das Formular vorgibt. ACHTUNG: Wird hier keine Konfiguration ausgewählt, wird versucht die Nutzerdaten für den Versandt des Downloadlinks vom Frontend-Nutzer zu beziehen.'];
$lang['watchlistItemFile']      = ['File-Item-Klasse','Die Item-Klasse, welche ein Watchlist-Item repräsentiert'];
$lang['watchlistItemEntity']    = ['Entity-Item-Klasse', ];

$lang['downloadItemFile']       = ['File-Download-Item-Klasse'];
$lang['downloadItemEntity']     = ['Entity-Download-Item-Klasse'];

/**
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';

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
