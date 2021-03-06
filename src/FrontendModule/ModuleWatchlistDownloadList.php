<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\FrontendModule;

use Contao\Controller;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\DownloadAllActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;

class ModuleWatchlistDownloadList extends ModuleList
{
    const MODULE_WATCHLIST_DOWNLOAD_LIST = 'huhwatchlist_downloadlist';
    const MODULE_WATCHLIST_DOWNLOAD_ACTIVATION = 'activation';
    const MODULE_WATCHLIST_DOWNLOAD_ACTIVATION_ACTIVATED = 'ACTIVATED';

    protected $strTemplate = 'mod_watchlist_download_list';

    public function __construct(ModuleModel $objModule, string $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);
        $this->container = System::getContainer();
    }

    public function generate()
    {
        if ($this->container->get('huh.request')->hasGet('file')) {
            $this->container->get('contao.framework')->getAdapter(Controller::class)->sendFileToBrowser($this->container->get('huh.request')->getGet('file'));
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

        if (!$this->activateWatchlist($watchlist)) {
            $this->Template->empty = System::getContainer()->get('translator')->trans('huh.watchlist.downloadlist.invalid_activation');
        }

        if (!$this->container->get('huh.watchlist.watchlist_manager')->checkWatchlistValidity($watchlist, $this)) {
            $this->Template->empty = System::getContainer()->get('translator')->trans('huh.watchlist.downloadlist.validity_expired');
        }

        if (0 === $this->container->get('contao.framework')->getAdapter(WatchlistItemModel::class)->countByPid($watchlist->id)) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST_ITEMS']['empty'];
        }

        if ($this->useDownloadAllAction && (null !== ($configuration = WatchlistConfigModel::findByPk($this->listConfig->watchlistConfig)))) {
            $this->Template->downloadAllAction = $this->container->get(PartialTemplateBuilder::class)->generate(
                new DownloadAllActionPartialTemplate($configuration, $watchlist)
            );
        }

        $queryBuilder = $this->getFilterConfig()->getQueryBuilder();
        $query = $this->filter->dataContainer.'.uuid='.$watchlistUuid;

        $queryBuilder->add('where', $query, true);

        parent::compile();
    }

    protected function activateWatchlist($watchlist)
    {
        if (!$watchlist->activation) {
            return true;
        }

        if (false !== strpos($watchlist->activation, static::MODULE_WATCHLIST_DOWNLOAD_ACTIVATION_ACTIVATED)) {
            return true;
        }

        if ('' == ($activation = $this->container->get('huh.request')->getGet(static::MODULE_WATCHLIST_DOWNLOAD_ACTIVATION))) {
            return false;
        }

        if ($watchlist->activation != $activation) {
            return false;
        }

        $watchlist->activation = static::MODULE_WATCHLIST_DOWNLOAD_ACTIVATION_ACTIVATED.':'.$watchlist->activation;
        $watchlist->save();

        return true;
    }
}
