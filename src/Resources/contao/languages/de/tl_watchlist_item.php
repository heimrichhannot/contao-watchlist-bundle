<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_watchlist_item'];

/*
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier einen Titel ein.';

$lang['type'][0] = 'Typ';
$lang['type'][1] = 'Wählen Sie hier einen Typ aus.';

$lang['file'][0] = 'Datei';
$lang['file'][1] = 'Wählen Sie hier eine Datei aus.';

$lang['entityTable'][0] = 'Data-Container';
$lang['entityTable'][1] = 'Wählen Sie hier einen Data-Container aus.';

$lang['entity'][0] = 'Entität';
$lang['entity'][1] = 'Wählen Sie hier die Entität aus.';

$lang['page'][0] = 'Seite';
$lang['page'][1] = 'In diesem Feld wird die Seite gespeichert, auf der das Objekt der Merkliste hinzugefügt wurde.';

$lang['autoItem'][0] = 'Auto-Item';
$lang['autoItem'][1] = 'In diesem Feld wird das Auto-Item der Seite gespeichert, auf der das Objekt der Merkliste hinzugefügt wurde.';

/*
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['reference_legend'] = 'Verknüpfte Daten';
$lang['context_legend'] = 'Kontext';

/*
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer::TYPE_FILE => 'Datei',
    \HeimrichHannot\WatchlistBundle\DataContainer\WatchlistItemContainer::TYPE_ENTITY => 'Datenbank-Entität',
];

/*
 * Buttons
 */
$lang['new'][0] = 'Neues Merklistenobjekt';
$lang['new'][1] = 'Merklistenobjekt erstellen';
$lang['edit'][0] = 'Merklistenobjekt bearbeiten';
$lang['edit'][1] = 'Merklistenobjekt ID %s bearbeiten';
$lang['delete'][0] = 'Merklistenobjekt löschen';
$lang['delete'][1] = 'Merklistenobjekt ID %s löschen';
$lang['show'][0] = 'Merklistenobjekt Details';
$lang['show'][1] = 'Merklistenobjekt-Details ID %s anzeigen';
