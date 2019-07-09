<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;

class WatchlistItemManager
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    protected $module;

    public function __construct(ContaoFrameworkInterface $framework, $module)
    {
        $this->framework = $framework;
        $this->module = $module;
    }

    /**
     * @param int $itemId
     */
    public function getWatchlistIdFromItem(int $itemId)
    {
        if (null === ($watchlistItem = $this->framework->getAdapter(WatchlistItemModel::class)->findInstanceByPk($itemId))) {
            return null;
        }

        return $watchlistItem->pid;
    }

    /**
     * * check if item has already been added to a watchlist.
     *
     * @param int         $watchlistId
     * @param string|null $itemUuid
     * @param int|null    $ptable
     * @param int|null    $ptableId
     *
     * @return bool
     */
    public function isItemInWatchlist(int $watchlistId, string $itemUuid = null, int $ptable = null, int $ptableId = null)
    {
        if (null === $itemUuid && null === $ptable && null === $ptableId) {
            return false;
        }

        if (null !== $itemUuid) {
            return $this->checkWatchlistForFile($watchlistId, $itemUuid);
        }

        if (null !== $ptable && null !== $ptableId) {
            return $this->checkWatchlistForEntity($watchlistId, $ptable, $ptableId);
        }

        return false;
    }

    /**
     * get copyright of an file.
     *
     * @param $file
     *
     * @return string
     */
    protected function getCopyRight($file)
    {
        $copyrights = StringUtil::deserialize($file, true);

        if (empty($copyrights)) {
            return '';
        }

        return implode(',', $copyrights);
    }

    /**
     * check item existence in watchlist on uuid.
     *
     * @param $watchlistId
     * @param $itemUuid
     *
     * @return bool
     */
    protected function checkWatchlistForFile($watchlistId, $itemUuid)
    {
        if (null === $this->framework->getAdapter(WatchlistItemModel::class)->findByPidAndUuid($watchlistId, $itemUuid)) {
            return false;
        }

        return true;
    }

    /**
     * check item existence in watchlist on ptable and ptableId.
     *
     * @param $watchlistId
     * @param $ptable
     * @param $ptableId
     *
     * @return bool
     */
    protected function checkWatchlistForEntity($watchlistId, $ptable, $ptableId)
    {
        if (null === $this->framework->getAdapter(WatchlistItemModel::class)->findByPidAndPtableAndPtableId($watchlistId, $ptable, $ptableId)) {
            return false;
        }

        return true;
    }
}
