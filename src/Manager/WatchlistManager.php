<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 19.03.18
 * Time: 11:34
 */

namespace HeimrichHannot\WatchlistBundle\Manager;


use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Model\Collection;

class WatchlistManager
{
    const WATCHLIST_SESSION_FE = 'WATCHLIST_SESSION_FE';
    const WATCHLIST_SESSION_BE = 'WATCHLIST_SESSION_BE';
    
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
        $this->framework      = $framework;
        $this->actionManger   = $actionManager;
    }
    
    
    /**
     * @param null $moduleId
     * @param null $watchlistId
     *
     * @return WatchlistModel|null
     */
    public function getWatchlistModel($moduleId = null, $watchlistId = null)
    {
        if(null !== $watchlistId)
        {
            return $this->framework->getAdapter(WatchlistModel::class)->findModelInstanceByPk($watchlistId);
        }
        
        if (FE_USER_LOGGED_IN) {
            return $this->getMultipleWatchlistModelByUserOrGroups($moduleId);
        }
        
        return $this->getMultipleWatchlistModelBySession();
    }
    
    /**
     * return
     *
     * @param int $moduleId
     *
     * @return WatchlistModel|null
     */
    public function getMultipleWatchlistModelByUserOrGroups(int $moduleId)
    {
        $module = $this->framework->getAdapter(WatchlistModel::class)->findModelInstanceByPk($moduleId);
        
        if ($module->useGroupWatchlist) {
            $watchlist = $this->getWatchlistByModuleConfig($module);
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
     * get one watchlist by groups set at module
     *
     * @param $module
     *
     * @return mixed|null
     */
    public function getWatchlistByModuleConfig($module)
    {
        if (null === ($watchlist = $this->getWatchlistBySession())) {
            return $this->getOneWatchlistByUserGroups($module->groupWatchlist);
        }
        
        return $watchlist;
    }
    
    
    /**
     * get one watchlist by user id
     *
     * @return mixed
     */
    public function getWatchlistByUser()
    {
        if(FE_USER_LOGGED_IN)
        {
            $user = FrontendUser::getInstance();
            return $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedByPid($user->id);
        }
        
        return $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedBySessionId(session_id());
    }
    
    
    /**
     * get one watchlist to id from session
     *
     * @return mixed|null
     */
    public function getWatchlistBySession()
    {
        if (null === ($watchlistId = Session::getInstance()->get(WatchlistModel::WATCHLIST_SELECT))) {
            return null;
        }
        
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            return null;
        }
        
        return $watchlist;
    }
    
    
    /**
     * get one watchlist by user groups
     *
     * @param $groups
     *
     * @return mixed|null
     */
    public function getOneWatchlistByUserGroups($groups)
    {
        if (null === ($user = $this->framework->getAdapter(MemberModel::class)->findOneByGroups($groups))) {
            return null;
        }
        
        if (null === ($watchlists = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedByPid($user->id))) {
            return null;
        }
        
        return $watchlists;
        
    }
    
    /**
     * get all watchlists by user groups
     *
     * @param $groups
     *
     * @return mixed|null
     */
    public function getAllWatchlistsByUserGroups($groups)
    {
        if (null === ($user = $this->framework->getAdapter(MemberModel::class)->findByGroups($groups))) {
            return null;
        }
        
        if (null === ($watchlists = $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids($user->fetchEach('id')))) {
            return null;
        }
        
        return $watchlists;
    }
    
    /**
     * returns a watchlist from anonymous user
     *
     * @return WatchlistModel|null
     */
    public function getMultipleWatchlistModelBySession()
    {
        $ip          = (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '');
        $name        = FE_USER_LOGGED_IN ? static::WATCHLIST_SESSION_FE : static::WATCHLIST_SESSION_BE;
        $hash        = sha1(session_id() . $ip . $name);
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
    
    
    public function getWatchlistModelByUserOrSession()
    {
        if (FE_USER_LOGGED_IN === true) {
            $watchlistModel = $this->getWatchlistModelByUserId(FrontendUser::getInstance()->id);
        } else {
            $watchlistModel = $this->getWatchlistModelBySession();
        }
        
        Session::getInstance()->set(WatchlistModel::WATCHLIST_SELECT, $watchlistModel->id);
        
        return $watchlistModel;
    }
    
    
    /**
     * returns an array of watchlist models where the members (pid) are in the same group as the given user group
     *
     * @return array
     */
    public function getAllWatchlistByUserGroups($groups)
    {
        $watchlist       = [];
        $userGroups      = StringUtil::deserialize(\FrontendUser::getInstance()->groups, true);
        $groups          = StringUtil::deserialize($groups, true);
        $watchlistGroups = array_intersect($userGroups, $groups);
        
        if (!$watchlistGroups) {
            return $watchlist;
        }
        
        if (null === ($user = $this->framework->getAdapter(MemberModel::class)->findActiveByGroups($watchlistGroups))) {
            return $watchlist;
        }
        
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids($user->fetchEach('id')))) {
            return [];
        }
        
        return $watchlist;
    }
    
    /**
     * get the watchlist by user id
     * create a new one if none was found
     *
     * @param $id
     *
     * @return WatchlistModel|mixed
     */
    protected function getWatchlistModelByUserId($id)
    {
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($id))) {
            $name = FE_USER_LOGGED_IN ? static::WATCHLIST_SESSION_FE : static::WATCHLIST_SESSION_BE;
            
            return $this->actionManger->createWatchlist($name);
        }
        
        return $watchlist;
    }
    
    /**
     * returns a watchlist from anonymous user
     *
     * @return \Model|null
     */
    protected function getWatchlistModelBySession()
    {
        $ip          = (!Config::get('disableIpCheck') ? Environment::get('ip') : '');
        $name        = FE_USER_LOGGED_IN ? static::WATCHLIST_SESSION_FE : static::WATCHLIST_SESSION_BE;
        $hash        = sha1(session_id() . $ip . $name);
        $watchlistModel = $this->framework->getAdapter(WatchlistModel::class)->findByHashAndName($hash, $name);
       
        if ($watchlistModel === null) {
            $watchlistModel = $this->actionManger->createWatchlist($name,$hash);
        }
        
        return $watchlistModel;
    }
    
    
    /**
     * @param $watchlist
     * @param $moduleId
     *
     * @return string
     */
    public function getMultipleWatchlist($watchlist, $moduleId)
    {
        $objT = new FrontendTemplate('watchlist_multiple');
        
        $objT->action            = AjaxAction::generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_SELECT_ACTION);
        $objT->watchlistHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['headline'];
        $module                  = $this->framework->getAdapter(ModuleModel::class)->findById($moduleId);
        
        if (null === $watchlist || null === $module) {
            $objT->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
            
            return $objT->parse();
        }
        
        if ($module->useGroupWatchlist) {
            $objT->select = $this->getAllWatchlistsByCurrentUser(true, $module->groupWatchlist);
        } else {
            $objT->select = $this->getAllWatchlistsByCurrentUser(true);
        }
        
        $objT->selected = Session::getInstance()->get(static::WATCHLIST_SELECT);
        $items          = WatchlistItemModel::findBy('pid', $watchlist->id);
        
        $objT->deleteWatchlistAction = $this->getDeleteWatchlistAction();
        if ($items == null || $items->count() <= 0) {
            $objT->empty = $GLOBALS['TL_LANG']['WATCHLIST_ITEMS']['empty'];
            
            return $objT->parse();
        }
        $watchlistController = new WatchlistController();
        foreach ($items as $item) {
            if ($item->type == WatchlistItemModel::WATCHLIST_ITEM_TYPE_DOWNLOAD) {
                $objT->downloadAllAction = $this->getDownloadAllAction($watchlistController->downloadAll($watchlist));
                break;
            }
        }
        $objT->watchlist = $this->getWatchlist($module, $items, false);
        
        return $objT->parse();
    }
    
    /**
     * find all watchlist models by current user
     *
     * @param bool $showDurability
     * @param bool $groups
     *
     * @return array
     */
    public function getAllWatchlistsByCurrentUser($showDurability = false, $groups = false)
    {
        $watchlistArray = [];
        if (FE_USER_LOGGED_IN === true) {
            $user = FrontendUser::getInstance();
            
            if ($groups) {
                $watchlist =  $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids(deserialize($user->groups,true));
            } else {
                $watchlist =  $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids([$user->id]);
            }
        } else {
            $watchlist = $this->getMultipleWatchlistModelBySession();
        }
        
        if (null === $watchlist) {
            return $watchlistArray;
        }
        
        foreach ($watchlist as $value) {
            if($showDurability)
            {
                if($value->start <= 0 || $value->stop <= 0)
                {
                    $watchlistArray[$value->id] = $value->name . ' ( ' . $GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal'] . ' )';
                    continue;
                }
    
                $durability = date('d.m.Y', $value->stop);
                if ($durability > date('d.m.Y', time())) {
                    static::unsetWatchlist($value->id);
                    continue;
                }
                $watchlistArray[$value->id] = $value->name . ' ( ' . $durability . ' )';
                continue;
            }
    
            $watchlistArray[$value->id] = $value->name;
            
        }
        
        return $watchlistArray;
    }
    
    
    protected function getAllWatchlistByCurrentUser($id)
    {
        if(null === ($watchlists = $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids([$id])))
        {
            return null;
        }
        
        
        
    }
    
    /**
     * returns an array of watchlist models where the members (pid) are in the same group as the current user
     *
     * @return array
     */
    public static function getAllWatchlistByCurrentUserGroups()
    {
        $watchlist       = [];
        $publicWatchlist = static::findPublished();
        if ($publicWatchlist == null) {
            return $watchlist;
        }
        foreach ($publicWatchlist as $watchlistModel) {
            $memberModel = \MemberModel::findById($watchlistModel->pid);
            if ($memberModel === null) {
                continue;
            }
            $watchlistGroups = StringUtil::deserialize($memberModel->groups, true);
            $groups          = StringUtil::deserialize(\FrontendUser::getInstance()->groups, true);
            if (array_intersect($watchlistGroups, $groups)) {
                $watchlist[] = $watchlistModel;
            }
        }
        
        return $watchlist;
    }
    
    /**
     * @param $module
     *
     * @return bool
     */
    public function checkPermission($module)
    {
        if(!$module->protected)
        {
            return true;
        }
    
        if(null === ($user = FrontendUser::getInstance()))
        {
            return false;
        }
    
        if (!array_intersect(StringUtil::deserialize($module->groups,true), StringUtil::deserialize($user->groups,true)))
        {
            return false;
        }
    
        return true;
    }
}