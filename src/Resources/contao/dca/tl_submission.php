<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 21.01.19
 * Time: 16:44
 */

$dca = &$GLOBALS['TL_DCA']['tl_submission'];

$dca['config']['onsubmit_callback'][] = ['huh.watchlist.event_listener.watchlist_download_listener', 'sendDownloadLink'];
