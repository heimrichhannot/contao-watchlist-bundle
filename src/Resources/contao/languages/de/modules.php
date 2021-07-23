<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

/**
 * Backend modules.
 */
$GLOBALS['TL_LANG']['MOD']['watchlist'][0] = 'Merklisten';
$GLOBALS['TL_LANG']['MOD']['watchlist_config'][0] = 'Merklistenkonfigurationen';

/*
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Controller\FrontendModule\WatchlistModuleController::TYPE][0] = 'Merkliste';
$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Controller\FrontendModule\WatchlistModuleController::TYPE][1] = 'Fügt eine Merkliste samt Schaltfläche zum Öffnen derselbigen hinzu.';

$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Controller\FrontendModule\ShareListModuleController::TYPE][0] = 'Teilen-Liste (Merkliste)';
$GLOBALS['TL_LANG']['FMD'][\HeimrichHannot\WatchlistBundle\Controller\FrontendModule\ShareListModuleController::TYPE][1] = 'Zeigt öffentlich die Einträge einer Merkliste an, die per GET-Parameter übergeben wird.';
