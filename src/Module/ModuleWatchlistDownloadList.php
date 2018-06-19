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
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;

class ModuleWatchlistDownloadList extends ModuleList
{
    const MODULE_WATCHLIST_DOWNLOAD_LIST = 'huhwatchlist_downloadlist';
    protected $strTemplate = 'mod_watchlist_download_list';

    public function __construct(ModuleModel $objModule, string $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);
        $this->container = System::getContainer();
    }

    public function generate()
    {
        if ($this->container->get('huh.request')->hasGet('file')) {
            $this->container->get('contao.framework')->getAdapter(\Contao\Controller::class)->sendFileToBrowser($this->container->get('huh.request')->getGet('file'));
        }

        return parent::generate();
    }

    protected function compile()
    {
        if (null === ($watchlistUuid = $this->container->get('huh.request')->getGet('watchlist'))) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }

        if (null === ($watchlist = $this->container->get('huh.watchlist.watchlist_manager')->getWatchlistByUuid($watchlistUuid))) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }

        if (!$this->container->get('huh.watchlist.watchlist_manager')->checkWatchlistValidity($watchlist)) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['validityExpired'];
        }

        if (0 === $this->container->get('contao.framework')->getAdapter(WatchlistItemModel::class)->countByPid($watchlist->id)) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST_ITEMS']['empty'];
        }

        $this->Template->downloadAllAction = $this->container->get('huh.watchlist.template_manager')->getDownloadAllAction($watchlist->id, $this->id);

        parent::compile();
    }
}
