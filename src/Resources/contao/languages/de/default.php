<?php

/**
 * Actions
 */
$GLOBALS['TL_LANG']['WATCHLIST']['delWatchlistLink']       = 'Liste löschen';
$GLOBALS['TL_LANG']['WATCHLIST']['delWatchlistTitle']      = 'Alle Elemente von der Merkliste entfernen';
$GLOBALS['TL_LANG']['WATCHLIST']['downloadAll']            = 'alles herunterladen';
$GLOBALS['TL_LANG']['WATCHLIST']['downloadAllSecondTitle'] = 'Alle Elemente als ZIP Archiv herunterladen';
$GLOBALS['TL_LANG']['WATCHLIST']['selectOption']           = [
    'Wählen Sie eine Option',
    \HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager::WATCHLIST_SELECT_ITEM_OPTIONS      => 'Wählen Sie eine Option',
    \HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager::WATCHLIST_SELECT_WATCHLIST_OPTIONS => 'Wählen Sie eine Merkliste',
];

$GLOBALS['TL_LANG']['WATCHLIST']['watchlist']           = 'Merkliste';
$GLOBALS['TL_LANG']['WATCHLIST']['watchlist_name']      = 'Name';

/**
 * Links
 */
$GLOBALS['TL_LANG']['WATCHLIST']['toggleLink'] = 'Merkliste';
$GLOBALS['TL_LANG']['WATCHLIST']['closeLink']  = 'Schliessen';

/**
 * Text
 */
$GLOBALS['TL_LANG']['WATCHLIST']['empty']                = 'Keine Liste vorhanden';
$GLOBALS['TL_LANG']['WATCHLIST_ITEMS']['empty']          = 'Keine Elemente vorhanden';
$GLOBALS['TL_LANG']['WATCHLIST']['headline']             = 'Ihre Merkliste';
$GLOBALS['TL_LANG']['WATCHLIST']['newWatchlist']         = 'Neue Merkliste anlegen';
$GLOBALS['TL_LANG']['WATCHLIST']['selectWatchlist']      = 'Merkliste auswählen';
$GLOBALS['TL_LANG']['WATCHLIST']['watchlistModalTitle']  = "<span class=\"title\">%s</span> der Merkliste hinzufügen.";
$GLOBALS['TL_LANG']['WATCHLIST']['abort']                = 'Abbrechen';
$GLOBALS['TL_LANG']['WATCHLIST']['download']             = 'Download';
$GLOBALS['TL_LANG']['WATCHLIST']['noDownload']           = 'Diese Datei ist nicht zum Download freigegeben. Wenden Sie sich bitte an den Urheber der Datei.';
$GLOBALS['TL_LANG']['WATCHLIST']['downloadListHeadline'] = 'Beispieltitel';
$GLOBALS['TL_LANG']['WATCHLIST']['addToWatchlist']       = 'Einer Merkliste hinzufügen';
$GLOBALS['TL_LANG']['WATCHLIST']['modalHeadlineConfig']  = 'Merkliste: %s';
$GLOBALS['TL_LANG']['WATCHLIST']['validityExpired']      = 'Diese Merkliste ist nicht mehr einsehbar.';
$GLOBALS['TL_LANG']['WATCHLIST']['invalidActivation']    = 'Ihnen fehlt die Berechtigung diese Merkliste einzusehen.';



/**
 * durability of a watchlist
 */
$GLOBALS['TL_LANG']['WATCHLIST']['durability']['label']    = 'Lebensdauer ';
$GLOBALS['TL_LANG']['WATCHLIST']['durability']['days']     = ' Tage';
$GLOBALS['TL_LANG']['WATCHLIST']['durability']['default']  = '30 Tage';
$GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal'] = 'Unendlich';


/**
 * Notifications
 */
$GLOBALS['TL_LANG']['WATCHLIST']['message_update_item']                        = "<span class=\"title\">%s</span> wurde in der Merkliste aktualisiert.";
$GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all']                         = "Die Merkliste wurde gelöscht.";
$GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_error']                   = "Es ist ein Fehler beim löschen der Merkliste aufgetreten.";
$GLOBALS['TL_LANG']['WATCHLIST']['message_add_watchlist_error']                = "' <span class=\"title\">%s</span> ' konnte nicht erstellt werden.";
$GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_exists_error']             = "Die Merkliste ' <span class=\"title\">%s</span> ' existiert bereits.";
$GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_items']                   = "Es wurden alle Items der Liste gelöscht";
$GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_items_from_watchlist']    = "Es wurden alle Items der Liste <span class=\"title\">%s</span> gelöscht";
$GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_download_link_error']      = "Es ist ein Fehler beim generieren des Links aufgetreten.";
$GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_download_link_page_error'] = "Die Zielseite des Links existiert nicht.";
$GLOBALS['TL_LANG']['WATCHLIST']['message_invalid_file']                       = 'Die gewählte Datei ist fehlerhaft und kann nicht hinzugefügt werden';
$GLOBALS['TL_LANG']['WATCHLIST']['message_no_watchlist_found']                 = 'Die gewählte Merkliste konnte nicht gefunden werden.';
$GLOBALS['TL_LANG']['WATCHLIST']['message_no_data']                            = 'Es wurden keine Daten für das hinzuzufügende Item übertragen.';
$GLOBALS['TL_LANG']['WATCHLIST']['message_empty_watchlist']                    = 'Die Merkliste wurde erfolgreich geleert.';
$GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_already_exists']           = 'Es existiert bereits eine Merkliste mit dem Namen "%s".';
$GLOBALS['TL_LANG']['WATCHLIST']['message_no_module']                          = 'Es wurde keine Konfiguration gefunden.';
$GLOBALS['TL_LANG']['WATCHLIST']['message_config_error']                       = 'Die Konfiguration ist fehlerhaft.';
$GLOBALS['TL_LANG']['WATCHLIST']['message_no_user']                            = 'Es wurden keine Nutzerdaten gefunden.';
$GLOBALS['TL_LANG']['WATCHLIST']['message_notofication_error']                 = 'Es wurde keine Benachrichtigungs-Konfiguration gefunden.';
