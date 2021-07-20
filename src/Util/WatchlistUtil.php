<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Util;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use Contao\Model;
use Contao\System;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class WatchlistUtil
{
    protected ContaoFramework $framework;
    protected DatabaseUtil    $DatabaseUtil;
    protected Utils           $utils;
    protected ModelUtil       $modelUtil;

    public function __construct(ContaoFramework $framework, DatabaseUtil $databaseUtil, Utils $utils, ModelUtil $modelUtil)
    {
        $this->framework = $framework;
        $this->databaseUtil = $databaseUtil;
        $this->utils = $utils;
        $this->modelUtil = $modelUtil;
    }

    public function createWatchlist(string $title, int $config, array $options = []): ?Model
    {
        $data = $options['data'] ?? [];

        $watchlist = new WatchlistModel();

        $time = time();

        $watchlist->setRow([
            'tstamp' => $time,
            'dateAdded' => $time,
            'title' => $title,
            'uuid' => md5(uniqid(rand(), true)),
            'config' => $config,
        ]);

        // avoid having duplicate uuids
        while (null !== $this->modelUtil->findOneModelInstanceBy('tl_watchlist', ['tl_watchlist.uuid=?'], [$watchlist->uuid])) {
            $watchlist->uuid = md5(uniqid(rand(), true));
        }

        // set author
        if ($this->utils->container()->isBackend()) {
            // bind to user
            $watchlist->authorType = DcaUtil::AUTHOR_TYPE_USER;
            $watchlist->author = BackendUser::getInstance()->id;
        } else {
            if (FE_USER_LOGGED_IN) {
                // bind to member
                $watchlist->authorType = DcaUtil::AUTHOR_TYPE_MEMBER;
                $watchlist->author = FrontendUser::getInstance()->id;
            } else {
                // session
                $watchlist->authorType = DcaUtil::AUTHOR_TYPE_SESSION;
                $watchlist->author = session_id();
            }
        }

        foreach ($data as $field => $value) {
            $watchlist->{$field} = $value;
        }

        $watchlist->save();

        return $watchlist;
    }

    public function addItemToWatchlist(array $data, int $watchlist, array $options = []): ?Model
    {
        $watchlistItem = new WatchlistItemModel();

        $time = time();

        $watchlistItem->setRow([
            'tstamp' => $time,
            'dateAdded' => $time,
            'pid' => $watchlist,
        ]);

        foreach ($data as $field => $value) {
            $watchlistItem->{$field} = $value;
        }

        $watchlistItem->save();

        return $watchlistItem;
    }

    /**
     * @return Model
     */
    public function getCurrentWatchlist(array $options = []): ?Model
    {
        $this->framework->getAdapter(System::class)->loadLanguageFile('default');

        $createIfNotExisting = $options['createIfNotExisting'] ?? false;
        $rootPage = $options['rootPage'] ?? 0;

        if (null === ($config = $this->getCurrentWatchlistConfig($rootPage))) {
            return null;
        }

        // create search criteria
        if ($this->utils->container()->isBackend()) {
            $columns = [
                'tl_watchlist.authorType=?',
                'tl_watchlist.author=?',
            ];

            $values = [
                DcaUtil::AUTHOR_TYPE_USER,
                BackendUser::getInstance()->id,
            ];
        } else {
            if (FE_USER_LOGGED_IN) {
                $columns = [
                    'tl_watchlist.authorType=?',
                    'tl_watchlist.author=?',
                ];

                $values = [
                    DcaUtil::AUTHOR_TYPE_MEMBER,
                    FrontendUser::getInstance()->id,
                ];
            } else {
                $columns = [
                    'tl_watchlist.authorType=?',
                    'tl_watchlist.author=?',
                ];

                $values = [
                    DcaUtil::AUTHOR_TYPE_SESSION,
                    session_id(),
                ];
            }
        }

        if (null !== ($watchlist = $this->modelUtil->findOneModelInstanceBy('tl_watchlist', $columns, $values))) {
            return $watchlist;
        }

        if (!$createIfNotExisting) {
            return null;
        }

        return $this->createWatchlist($GLOBALS['TL_LANG']['MSC']['watchlistBundle']['watchlist'], (int) $config->id);
    }

    public function getCurrentWatchlistConfig(int $rootPage = 0): ?Model
    {
        if (!$rootPage) {
            global $objPage;

            $rootPage = $objPage->rootId;
        }

        if (null === ($page = $this->databaseUtil->findResultByPk('tl_page', $rootPage)) || $page->numRows < 1) {
            return null;
        }

        return $this->modelUtil->findModelInstanceByPk('tl_watchlist_config', $page->watchlistConfig);
    }

    public function getWatchlistItems(int $watchlist): array
    {
        if (null === ($items = $this->databaseUtil->findResultsBy('tl_watchlist_item', ['tl_watchlist_item.pid=?'], [$watchlist])) || $items->numRows < 1) {
            return [];
        }

        return $items->fetchAllAssoc();
    }

    public function getWatchlistItem(int $id, int $watchlist): ?Model
    {
        if (null === ($item = $this->databaseUtil->findOneResultBy('tl_watchlist_item',
                ['tl_watchlist_item.pid=?', 'tl_watchlist_item.id=?'], [$watchlist, $id])) || $item->numRows < 1) {
            return null;
        }

        return $item;
    }
}