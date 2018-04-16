<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class WatchlistManager
{
    const WATCHLIST_SESSION_FE = 'WATCHLIST_SESSION_FE';
    const WATCHLIST_SESSION_BE = 'WATCHLIST_SESSION_BE';

    const WATCHLIST_ITEM_FILE_GROUP = 'watchlistFileItems';
    const WATCHLIST_ITEM_ENTITY_GROUP = 'watchlistFileItems';
    const WATCHLIST_DOWNLOAD_FILE_GROUP = 'downloadFileItems';
    const WATCHLIST_DOWNLOAD_ENTITY_GROUP = 'downloadFileItems';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var WatchlistActionManager
     */
    protected $actionManger;

    public function __construct(ContaoFrameworkInterface $framework, WatchlistActionManager $actionManager)
    {
        $this->framework = $framework;
        $this->actionManger = $actionManager;
    }

    /**
     * @param null $moduleId
     * @param null $watchlistId
     *
     * @return WatchlistModel|null
     */
    public function getWatchlistModel($moduleId = null, $watchlistId = null)
    {
        if ($watchlistId) {
            return $this->framework->getAdapter(WatchlistModel::class)->findModelInstanceByPk($watchlistId);
        }

        if (!$moduleId) {
            return null;
        }

        if (FE_USER_LOGGED_IN) {
            return $this->getWatchlistByUserOrGroups($moduleId);
        }

        return $this->getWatchlistBySession();
    }

    /**
     * return.
     *
     * @param int $moduleId
     *
     * @return WatchlistModel|null
     */
    public function getWatchlistByUserOrGroups(int $moduleId)
    {
        if (null === ($module = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module', $moduleId))) {
            return null;
        }

        if ($module->useGroupWatchlist) {
            $watchlist = $this->getWatchlistByGroups($module);
        } else {
            $watchlist = $this->getWatchlistByUser();
        }

        if (null === $watchlist) {
            $watchlist = $this->actionManger->createWatchlist($GLOBALS['TL_LANG']['WATCHLIST']['watchlist']);
        }

        Session::getInstance()->set(WatchlistModel::WATCHLIST_SELECT, $watchlist->id);

        return $watchlist;
    }

    /**
     * @param $module
     *
     * @return WatchlistModel|null
     */
    public function getWatchlistByGroups($module)
    {
        $groups = StringUtil::deserialize($module->groups, true);

        if (!$module->protected) {
            return $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids($groups);
        }

        if (null === ($user = FrontendUser::getInstance())) {
            return null;
        }

        if (!($intersect = array_intersect($groups, StringUtil::deserialize($user->groups, true)))) {
            return null;
        }

        return $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids($intersect);
    }

    /**
     * get one watchlist by user id.
     *
     * @return mixed
     */
    public function getWatchlistByUser()
    {
        if (FE_USER_LOGGED_IN) {
            $user = FrontendUser::getInstance();

            return $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedByPid($user->id);
        }

        return $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedBySessionId(session_id());
    }

    /**
     * returns a watchlist from anonymous user.
     *
     * @return WatchlistModel|null
     */
    public function getWatchlistBySession()
    {
        $ip = (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '');
        $name = FE_USER_LOGGED_IN ? static::WATCHLIST_SESSION_FE : static::WATCHLIST_SESSION_BE;
        $hash = sha1(session_id().$ip.$name);
        $watchlistId = Session::getInstance()->get(WatchlistModel::WATCHLIST_SELECT);

        if (null === $watchlistId) {
            $watchlist = $this->framework->getAdapter(WatchlistModel::class)->findByHashAndName($hash, $name);
        } else {
            $watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId);
        }

        if (null === $watchlist) {
            $watchlist = $this->actionManger->createWatchlist($name);
        }

        Session::getInstance()->set(WatchlistModel::WATCHLIST_SELECT, $watchlist->id);

        return $watchlist;
    }

    public function getWatchlistByName($name)
    {
        $id = session_id();

        if (null !== ($user = FrontendUser::getInstance())) {
            $id = $user->id;
        }

        return $this->framework->getAdapter(WatchlistModel::class)->findByNameAndPid($name, $id);
    }

    /**
     * find all watchlist models by current user.
     *
     * @param bool $showDurability
     * @param bool $groups
     *
     * @return array
     */
    public function getWatchlistByCurrentUser($showDurability = false, $groups = false)
    {
        $watchlistArray = [];
        if (FE_USER_LOGGED_IN === true) {
            $user = FrontendUser::getInstance();

            if ($groups) {
                $watchlist = $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids(deserialize($user->groups, true));
            } else {
                $watchlist = $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids([$user->id]);
            }
        } else {
            $watchlist = $this->getWatchlistBySession();
        }

        if (null === $watchlist) {
            return $watchlistArray;
        }

        foreach ($watchlist as $value) {
            if ($showDurability) {
                if ($value->start <= 0 || $value->stop <= 0) {
                    $watchlistArray[$value->id] = $value->name.' ( '.$GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal'].' )';
                    continue;
                }

                $durability = date('d.m.Y', $value->stop);
                if ($durability > date('d.m.Y', time())) {
                    static::unsetWatchlist($value->id);
                    continue;
                }
                $watchlistArray[$value->id] = $value->name.' ( '.$durability.' )';
                continue;
            }

            $watchlistArray[$value->id] = $value->name;
        }

        return $watchlistArray;
    }

    /**
     * @param $module
     *
     * @return bool
     */
    public function checkPermission($module)
    {
        if (!$module->protected) {
            return true;
        }

        if (null === ($user = FrontendUser::getInstance())) {
            return false;
        }

        if (!array_intersect(StringUtil::deserialize($module->groups, true), StringUtil::deserialize($user->groups, true))) {
            return false;
        }

        return true;
    }

    /**
     * get current watchlist.
     *
     * @param $module
     *
     * @return string
     */
    public function getCurrentWatchlistItems($module, $watchlistId = null)
    {
        if (null === $module && null === $watchlistId) {
            return null;
        }

        if ($watchlistId
            && null !== ($items = $this->getItemsFromWatchlist($watchlistId))) {
            return $items;
        }

        if ($module->useGroupWatchlist) {
            $watchlist = $this->getWatchlistByModuleConfig($module);
        } else {
            $watchlist = $this->getWatchlistByUser();
        }

        if (null === $watchlist) {
            return null;
        }

        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlist->id))) {
            return null;
        }

        return $watchlistItems;
    }

    /**
     * get all options for multiple watchlists.
     *
     * @param $module
     *
     * @return array
     */
    public function getWatchlistOptions($module)
    {
        if ($module->useGroupWatchlist) {
            $watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistByGroups($module);
        } else {
            $watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistByCurrentUser();
        }

        if (empty($watchlist)) {
            return [];
        }

        if ($module->useWatchlistDurability) {
            $options = [];

            foreach ($watchlist as $model) {
                $durability = $model->watchlistDurability * 86400;
                if (($model->tstamp + $durability) < time()) {
                    continue;
                }

                $options[] = $model;
            }

            $watchlist = $options;
        }

        return $watchlist;
    }

    /**
     * get class for watchlist items and download items.
     *
     * @param string $context
     * @param string $name
     *
     * @return null|string
     */
    public function getClassByName(string $name, string $context): ?string
    {
        $config = System::getContainer()->getParameter('huh.watchlist');

        if (!isset($config['watchlist'][$context])) {
            return null;
        }

        $items = $config['watchlist'][$context];

        foreach ($items as $item) {
            if ($item['name'] == $name) {
                return class_exists($item['class']) ? $item['class'] : null;
            }
        }

        return null;
    }

    /**
     * @param $watchlist
     *
     * @return mixed
     */
    public function getItemsFromWatchlist($watchlist)
    {
        return $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlist);
    }
}
