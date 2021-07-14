<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_watchlist_config'];

/*
 * Fields
 */
$lang['tstamp'][0] = 'Revision date';
$lang['title'][0] = 'Title';
$lang['title'][1] = 'Please enter a title.';

$lang['watchlistFrontendFramework'] = ['Watchlist frontend framework', 'Choose the framework responsible for presentation.'];
$lang['useMultipleWatchlist'] = ['Use multiple watchlists', 'Use and manage multiple watchlist'];
$lang['useGroupWatchlist'] = ['Member group list', 'Members of a member group will use and manage the same watchlist.'];
$lang['groupWatchlist'] = ['Allowed member groups', 'This groups can see and manage the watchlist.'];
$lang['useWatchlistDurability'] = ['Watchlist durability', 'Choose this option, if watchlists should have a limited span time. Only used in combination with muliple watchlists.'];
$lang['watchlistDurability'] = ['Durability', 'Life span in days.'];
$lang['useGlobalDownloadAllAction'] = ['Global download all action', 'Display the download all button outside the watchlist window.'];
$lang['disableDownloadAll'] = ['Disable "Download all" ', 'Disable the "Download all" button in watchlist window.'];
$lang['overrideWatchlistTitle'] = ['Custom watchlist name', 'Use a custom watchlist name.'];
$lang['watchlistTitle'] = ['Watchlist name', 'Select the watchlist name.'];
$lang['overrideTogglerTitle'] = ['Custom watchlist button text', 'Use a custom text for the button that opens the watchlist window.'];
$lang['togglerTitle'] = ['Watchlist button text', 'Choose the custom title of the button that opens the watchlist window.'];

$lang['watchlistItemFile'] = ['File item class', 'The item class representing an watchlist item.'];
$lang['watchlistItemEntity'] = ['Entity item class'];
$lang['downloadItemFile'] = ['File download item class'];
$lang['downloadItemEntity'] = ['Entity download item class'];

$lang['useDownloadLink'] = ['Show download link', 'Create a redirect link to a page providing all elements of an watchlist as download.'];
$lang['downloadLink'] = ['Redirect page for download link', 'Redirect page providing all elements of an watchlist as download.'];
$lang['downloadLinkUseNotification'] = ['Send downloadlink as notification', 'Select this option to send the download link as notificaiton (typically email).'];
$lang['downloadLinkNotification'] = ['Download link notification', 'Choose the notification that should be used to send the download link.'];
$lang['downloadLinkUseConfirmationNotification'] = ['Send confirmation notification', 'Select this option to send an confirmation notification to the user before sending the download link.'];
$lang['downloadLinkFormConfigModule'] = ['Form configuration', 'Select the form module containing the form configuration. ATTENTION: If no configuration is selected, the user data will be used for shipping the notification.'];

/*
 * Legends
 */
$lang['general_legend'] = 'General settings';
$lang['display_legend'] = 'Presentation';
$lang['additional_settings_legend'] = 'Additional settings';
$lang['item_legend'] = 'Items';
$lang['download_legend'] = 'Download';

/*
 * Buttons
 */
$lang['new'][0] = 'New Merklistenkonfiguration';
$lang['new'][1] = 'Merklistenkonfiguration create';
$lang['edit'][0] = 'Edit Merklistenkonfiguration';
$lang['edit'][1] = 'Edit Merklistenkonfiguration ID %s';
$lang['copy'][0] = 'Duplicate Merklistenkonfiguration';
$lang['copy'][1] = 'Duplicate Merklistenkonfiguration ID %s';
$lang['delete'][0] = 'Delete Merklistenkonfiguration';
$lang['delete'][1] = 'Delete Merklistenkonfiguration ID %s';
$lang['show'][0] = 'Merklistenkonfiguration details';
$lang['show'][1] = 'Show the details of Merklistenkonfiguration ID %s';

/*
 * Frontend Frameworks
 */

$lang['FRONTENDFRAMEWORK']['base'] = 'Base';
$lang['FRONTENDFRAMEWORK']['bs4'] = 'Bootstrap 4';
