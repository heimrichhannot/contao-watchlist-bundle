<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Module;

use Contao\PageModel;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\Request\Request;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class ModuleWatchlistDownloadList extends ModuleList
{
    const MODULE_WATCHLIST_DOWNLOAD_LIST = 'huhwatchlist_downloadlist';
    protected $strTemplate = 'mod_watchlist_download_list';

    protected function compile()
    {
        /* @var PageModel $objPage */
        global $objPage;

        $id = Request::getGet('watchlist');

        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findPublishedByUuid(Request::getGet('watchlist')))) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }

        if (!$this->checkWatchlistValidity($watchlist)) {
            /** @var \PageError404 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
        }

        $array = $this->getWatchlistItemsForDownloadList($watchlist);
        if (empty($array['items'])) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }
        $watchlistController = new WatchlistController();
        $this->Template->downloadAllButton = $array['downloadAllButton'];
        $this->Template->items = $array['items'];
        $this->Template->downloadAllHref = $watchlistController->downloadAll($watchlist);
        $this->Template->downloadAllLink = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAll'];
        $this->Template->downloadAllTitle = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllSecondTitle'];
        $this->Template->downloadListHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['downloadListHeadline'];
    }
}
