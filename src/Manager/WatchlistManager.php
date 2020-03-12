<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class WatchlistManager
{
    const WATCHLIST_SESSION_FE = 'WATCHLIST_SESSION_FE';
    const WATCHLIST_SESSION_BE = 'WATCHLIST_SESSION_BE';

    const WATCHLIST_ITEM_FILE_GROUP = 'watchlistFileItems';
    const WATCHLIST_ITEM_ENTITY_GROUP = 'watchlistEntityItems';
    const WATCHLIST_DOWNLOAD_FILE_GROUP = 'downloadFileItems';
    const WATCHLIST_DOWNLOAD_ENTITY_GROUP = 'downloadEntityItems';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var WatchlistActionManager
     */
    protected $actionManger;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var Session
     */
    private $session;

    public function __construct(ContainerInterface $container, ContaoFrameworkInterface $framework, WatchlistActionManager $actionManager, Session $session)
    {
        $this->framework = $framework;
        $this->actionManger = $actionManager;
        $this->container = $container;
        $this->session = $session;
    }

    /**
     * @return WatchlistModel|null
     */
    public function getWatchlistModel(?WatchlistConfigModel $configuration = null, ?int $watchlistId = null)
    {
        if ($watchlistId) {
            if (null !== ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findModelInstanceByPk($watchlistId))) {
                return $watchlist;
            }
        }

        if (!$configuration) {
            return null;
        }

        if (FE_USER_LOGGED_IN) {
            return $this->getWatchlistByUserOrGroups($configuration);
        }

        return $this->getWatchlistBySession();
    }

    /**
     * return.
     *
     * @param int $configuration
     *
     * @return WatchlistModel|Collection|null
     */
    public function getWatchlistByUserOrGroups(WatchlistConfigModel $configuration)
    {
        if ($configuration->useGroupWatchlist) {
            $watchlist = $this->getWatchlistByGroups($configuration);
        } else {
            $watchlist = $this->getWatchlistByUser();
        }

        if (null === $watchlist && !$configuration->useMultipleWatchlist) {
            $watchlist = $this->actionManger->createWatchlist($GLOBALS['TL_LANG']['WATCHLIST']['watchlist']);
        }

        if (null === $watchlist) {
            $this->session->set(WatchlistModel::WATCHLIST_SELECT, null);
        } else {
            $this->session->set(WatchlistModel::WATCHLIST_SELECT, $watchlist->id);
        }

        return $watchlist;
    }

    /**
     * @return WatchlistModel|null
     */
    public function getWatchlistByGroups(WatchlistConfigModel $configuration)
    {
        $groups = StringUtil::deserialize($configuration->groupWatchlist, true);

        return $this->framework->getAdapter(WatchlistModel::class)->findByUserGroups($groups);

        /*
         * @todo revert module user group restriction?
         */
//
//
//
//        if (!$configuration->protected) {
//            return $this->framework->getAdapter(WatchlistModel::class)->findByUserGroups($groups);
//        }
//
//        if (null === ($user = FrontendUser::getInstance())) {
//            return null;
//        }
//
//        if (!($intersect = array_intersect($groups, StringUtil::deserialize($user->groups, true)))) {
//            return null;
//        }
//
//        return $this->framework->getAdapter(WatchlistModel::class)->findByUserGroups($intersect);
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
        $ip = (!Config::get('disableIpCheck') ? Environment::get('ip') : '');
        $name = FE_USER_LOGGED_IN ? static::WATCHLIST_SESSION_FE : static::WATCHLIST_SESSION_BE;
        $hash = sha1(session_id().$ip.$name);
        $watchlistId = $this->session->get(WatchlistModel::WATCHLIST_SELECT);

        if (null === $watchlistId) {
            $watchlist = $this->framework->getAdapter(WatchlistModel::class)->findByHashAndName($hash, $name);
        } else {
            $watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId);
        }

        if (null === $watchlist) {
            $watchlist = $this->actionManger->createWatchlist($name);
        }

        $this->session->set(WatchlistModel::WATCHLIST_SELECT, $watchlist->id);

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
                $watchlist = $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids(deserialize($user->groups,
                    true));
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
                    $this->unsetWatchlist($value->id);
                    continue;
                }
                $watchlistArray[$value->id] = $value->name.' ( '.$durability.' )';
                continue;
            }

            $watchlistArray[$value->id] = $value->name;
        }

        return $watchlistArray;
    }

    public function getWatchlistByUuid(string $uuid)
    {
        return $this->framework->getAdapter(WatchlistModel::class)->findPublishedByUuid($uuid);
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

        if (!array_intersect(StringUtil::deserialize($module->groups, true),
            StringUtil::deserialize($user->groups, true))) {
            return false;
        }

        return true;
    }

    /**
     * get current watchlist.
     *
     * @return mixed|null
     */
    public function getCurrentWatchlistItems(WatchlistConfigModel $configuration, ?int $watchlistId = null)
    {
        if ($watchlistId && null !== ($items = $this->getItemsFromWatchlist($watchlistId))) {
            return $items;
        }

        if ($configuration->useGroupWatchlist) {
            $watchlist = $this->getWatchlistByGroups($configuration);
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
    public function getWatchlistOptions(WatchlistConfigModel $configuration)
    {
        $watchlist = $this->getWatchlistByUserOrGroups($configuration);

        if (empty($watchlist)) {
            return [];
        }

        if ($configuration->useWatchlistDurability) {
            $options = [];

            foreach ($watchlist as $model) {
                $durability = $configuration->watchlistDurability * 86400;
                if (($model->tstamp + $durability) < time()) {
                    continue;
                }

                $options[$model->id] = $model->name;
            }

            return $options;
        }

        return $watchlist->fetchEach('name');
    }

    /**
     * get class for watchlist items and download items.
     */
    public function getClassByName(string $name, string $context): ?string
    {
        $config = $this->container->getParameter('huh_watchlist');

        if (!isset($config[$context])) {
            return null;
        }

        $items = $config[$context];

        foreach ($items as $item) {
            if ($item['name'] == $name) {
                return class_exists($item['class']) ? $item['class'] : null;
            }
        }

        return null;
    }

    /**
     * @param $watchlistId
     */
    public function getItemsFromWatchlist($watchlistId): ?array
    {
        /*
         * @var WatchlistItemModel $watchlistItem
         */
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            return null;
        }

        $items = [];

        foreach ($watchlistItems as $watchlistItem) {
            if (null === System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($watchlistItem->ptable, $watchlistItem->ptableId)) {
                $watchlistItem->delete();
                continue;
            }

            $items[] = $watchlistItem;
        }

        return $items;
    }

    /**
     * @param WatchlistModel $watchlist
     *
     * @return bool
     */
    public function checkWatchlistValidity($watchlist, $module)
    {
        if (!$module->usePublicLinkDurability) {
            return true;
        }

        if (!$watchlist->startShare) {
            return false;
        }

        // publicLinkDurability is set in days at module
        $validityLimit = $watchlist->startShare + $module->publicLinkDurability;

        if (time() < $validityLimit) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getWatchlistName(WatchlistConfigModel $configuration, WatchlistModel $watchlist)
    {
        if ($configuration->overrideWatchlistTitle) {
            return $this->container->get('translator')->trans($configuration->watchlistTitle);
        }

        if (WatchlistTemplateManager::WATCHLIST_NAME_SUBMISSION == $watchlist->name) {
            return $this->container->get('translator')->trans('huh.watchlist.watchlist_label.default');
        }

        return  $watchlist->name;
    }
}
