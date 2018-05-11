<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Module;

use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\ListBundle\Module\ModuleList;

class ModuleWatchlistDownloadList extends ModuleList
{
    const MODULE_WATCHLIST_DOWNLOAD_LIST = 'huhwatchlist_downloadlist';
    protected $strTemplate = 'mod_watchlist_download_list';

    public function __construct(ModuleModel $objModule, string $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);
        $this->container = System::getContainer();
    }

    protected function compile()
    {
        if (null === ($watchlistUuid = $this->container->get('huh.request')->getGet('watchlist'))) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }

        if (null === ($watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistByUuid($watchlistUuid))) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }

        if (!System::getContainer()->get('huh.watchlist.watchlist_manager')->checkWatchlistValidity($watchlist)) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['validityExpired'];
        }

        $this->Template->downloadAllAction = System::getContainer()->get('huh.watchlist.template_manager')->getDownloadAllAction($watchlist->id, $this->id);

        parent::compile();
    }
}
