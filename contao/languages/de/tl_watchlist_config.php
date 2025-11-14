<?php

use HeimrichHannot\WatchlistBundle\Controller\FrontendModule\ShareListModuleController;

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_watchlist_config'];

/*
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier bitte den Titel ein.';

$lang['imgSize'][0] = 'Bildgröße';
$lang['imgSize'][1] = 'Hier können Sie die Abmessungen für die Bilder und den Skalierungsmodus festlegen.';

$lang['watchlistContentTemplate'][0] = 'Watchlist-Inhaltstemplate';
$lang['watchlistContentTemplate'][1] = 'Wählen Sie hier das gewünschte Template aus.';

$lang['insertTagAddItemTemplate'][0] = 'Insert-Tag-Template (Hinzufügen)';
$lang['insertTagAddItemTemplate'][1] = 'Wählen Sie hier das gewünschte Template aus.';

$lang['addShare'][0] = 'Teilen aktivieren';
$lang['addShare'][1] = 'Wählen Sie diese Option, um das Teilen von Merklisten zu erlauben.';

$lang['shareJumpTo'][0] = 'Weiterleitungsseite';
$lang['shareJumpTo'][1] = 'Wählen Sie hier die Basisseite für die Teilen-Links aus. Diese Seite muss ein Modul vom Typ "'.$GLOBALS['TL_LANG']['FMD'][ShareListModuleController::TYPE][0].'" enthalten.';

/*
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['share_legend'] = 'Teilen';
$lang['image_legend'] = 'Bildeinstellungen';

/*
 * Buttons
 */
$lang['new'][0] = 'Neue Merklistenkonfiguration';
$lang['new'][1] = 'Merklistenkonfiguration erstellen';
$lang['edit'][0] = 'Merklistenkonfiguration bearbeiten';
$lang['edit'][1] = 'Merklistenkonfiguration ID %s bearbeiten';
$lang['copy'][0] = 'Merklistenkonfiguration duplizieren';
$lang['copy'][1] = 'Merklistenkonfiguration ID %s duplizieren';
$lang['delete'][0] = 'Merklistenkonfiguration löschen';
$lang['delete'][1] = 'Merklistenkonfiguration ID %s löschen';
$lang['show'][0] = 'Merklistenkonfiguration Details';
$lang['show'][1] = 'Merklistenkonfiguration-Details ID %s anzeigen';
