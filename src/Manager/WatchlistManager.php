<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class WatchlistManager
{
    const WATCHLIST_SESSION_FE = 'WATCHLIST_SESSION_FE';
    const WATCHLIST_SESSION_BE = 'WATCHLIST_SESSION_BE';

    const WATCHLIST_ITEM_FILE_GROUP       = 'watchlistFileItems';
    const WATCHLIST_ITEM_ENTITY_GROUP     = 'watchlistFileItems';
    const WATCHLIST_DOWNLOAD_FILE_GROUP   = 'downloadFileItems';
    const WATCHLIST_DOWNLOAD_ENTITY_GROUP = 'downloadFileItems';

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
        $this->framework    = $framework;
        $this->actionManger = $actionManager;
        $this->container    = $container;
        $this->session      = $session;
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
            if (null !== ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findModelInstanceByPk($watchlistId))) {
                return $watchlist;
            }
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
     * @return WatchlistModel|Collection|null
     */
    public function getWatchlistByUserOrGroups(int $moduleId)
    {
        if (null === ($module = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module',
                $moduleId))) {
            return null;
        }

        if ($module->useGroupWatchlist) {
            $watchlist = $this->getWatchlistByGroups($module);
        } else {
            $watchlist = $this->getWatchlistByUser();
        }

        if (null === $watchlist && !$module->useMultipleWatchlist) {
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
     * @param $module
     *
     * @return WatchlistModel|null
     */
    public function getWatchlistByGroups($module)
    {
        $groups = StringUtil::deserialize($module->groups, true);

        if (!$module->protected) {
            return $this->framework->getAdapter(WatchlistModel::class)->findByUserGroups($groups);
        }

        if (null === ($user = FrontendUser::getInstance())) {
            return null;
        }

        if (!($intersect = array_intersect($groups, StringUtil::deserialize($user->groups, true)))) {
            return null;
        }

        return $this->framework->getAdapter(WatchlistModel::class)->findByUserGroups($intersect);
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
        $ip          = (!Config::get('disableIpCheck') ? Environment::get('ip') : '');
        $name        = FE_USER_LOGGED_IN ? static::WATCHLIST_SESSION_FE : static::WATCHLIST_SESSION_BE;
        $hash        = sha1(session_id() . $ip . $name);
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
                    $watchlistArray[$value->id] = $value->name . ' ( ' . $GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal'] . ' )';
                    continue;
                }

                $durability = date('d.m.Y', $value->stop);
                if ($durability > date('d.m.Y', time())) {
                    $this->unsetWatchlist($value->id);
                    continue;
                }
                $watchlistArray[$value->id] = $value->name . ' ( ' . $durability . ' )';
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
            $watchlist = $this->getWatchlistByGroups($module);
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
        $watchlist = $this->getWatchlistByUserOrGroups($module->id);

        if (empty($watchlist)) {
            return [];
        }

        if ($module->useWatchlistDurability) {
            $options = [];

            foreach ($watchlist as $model) {
                $durability = $module->watchlistDurability * 86400;
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
     *
     * @param string $context
     * @param string $name
     *
     * @return null|string
     */
    public function getClassByName(string $name, string $context): ?string
    {
        $config = System::getContainer()->getParameter('huh_watchlist');

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
     * @param $watchlist
     *
     * @return mixed
     */
    public function getItemsFromWatchlist($watchlist)
    {
        return $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlist);
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
     * @param ModuleModel $module
     * @param             $watchlist
     *
     * @return string
     */
    public function getWatchlistName(ModuleModel $module, $watchlist)
    {
        if ($module->overrideWatchlistTitle) {
            return $this->container->get('translator')->trans($module->watchlistTitle);
        }

        return WatchlistTemplateManager::WATCHLIST_NAME_SUBMISSION == $watchlist->name ? $GLOBALS['TL_LANG']['WATCHLIST']['modalHeadline'] : $watchlist->name;
    }
}
