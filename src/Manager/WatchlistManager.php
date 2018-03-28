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
        
        if(null === $moduleId)
        {
            return null;
        }
        
        if (FE_USER_LOGGED_IN) {
            return $this->getWatchlistByUserOrGroups($moduleId);
        }
        
        return $this->getWatchlistBySession();
    }
    
    /**
     * return
     *
     * @param int $moduleId
     *
     * @return WatchlistModel|null
     */
    public function getWatchlistByUserOrGroups(int $moduleId)
    {
        if(null === ($module = $this->framework->getAdapter(WatchlistModel::class)->findModelInstanceByPk($moduleId)))
        {
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
        $groups = StringUtil::deserialize($module->groups,true);
        
        if(!$module->protected)
        {
            return $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids($groups);
        }
        
        if(null === ($user = FrontendUser::getInstance()))
        {
            return null;
        }
        
        if(!($intersect = array_intersect($groups, StringUtil::deserialize($user->groups,true))))
        {
            return null;
        }
        
        return $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids($intersect);
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
     * returns a watchlist from anonymous user
     *
     * @return WatchlistModel|null
     */
    public function getWatchlistBySession()
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
    
    
    /**
     * find all watchlist models by current user
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
                $watchlist =  $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids(deserialize($user->groups,true));
            } else {
                $watchlist =  $this->framework->getAdapter(WatchlistModel::class)->findPublishedByPids([$user->id]);
            }
        } else {
            $watchlist = $this->getWatchlistBySession();
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