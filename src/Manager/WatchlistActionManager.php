<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.03.18
 * Time: 08:43
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Session;
use Contao\StringUtil;
use Contao\Validator;
use Contao\ZipWriter;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;

class WatchlistActionManager
{
    const MESSAGE_STATUS_ERROR   = 'watchlist-notify-error';
    const MESSAGE_STATUS_SUCCESS = 'watchlist-notify-success';
    
    /**
     * for tracking iterations
     *
     * @var int
     */
    protected $position = 0;
    
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    
    
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }
    
    /**
     * @param $name
     * @param $durability
     *
     * @return WatchlistModel
     */
    public function addMultipleWatchlist(string $name, string $durability)
    {
        return $this->createWatchlist($name, null, $durability);
    }
    
    /**
     * @param string         $uuid
     * @param WatchlistModel $watchlist
     * @param int            $cid
     * @param                $type
     * @param int            $pageID
     * @param string         $title
     *
     * @return string
     * @throws \Exception
     */
    public function addWatchlistItem(string $uuid, WatchlistModel $watchlist, int $cid, $type, int $pageID, string $title)
    {
        // Throw an exception if there's no id:
        if (!Validator::isStringUuid($uuid)) {
            throw new \Exception('The watchlist requires items with an unique file uuid.');
        }
        
        if (null !== ($watchlistItem = $this->framework->getAdapter(WatchlistItemModel::class)->findByUuid($uuid))
            && $watchlist->id == $watchlistItem->pid) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_in_watchlist'], $watchlistItem->title, $watchlist->name);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        $item         = new WatchlistItemModel();
        $item->pid    = $watchlist->id;
        $item->uuid   = \StringUtil::uuidToBin($uuid); // transform string to bin
        $item->pageID = $pageID;
        $item->cid    = $cid;
        $item->type   = $type;
        $item->tstamp = time();
        $item->title  = $title;
        $item->save();
        
        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_add_item'], $item->title);
        
        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }
    
    /**
     * delete a watchlist
     *
     * @param int $id
     *
     * @return string
     */
    public function deleteWatchlistItem(int $id)
    {
        if (null === ($watchlistItem = $this->framework->getAdapter(WatchlistItemModel::class)->findOnePublishedById($id))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_item_error']);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        $watchlistItem->delete();
        
        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_item'], $watchlistItem->title);
        
        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }
    
    /**
     * delete all items from specific watchlist
     *
     * @param int $watchlistId
     *
     * @return string
     */
    public function deleteWatchlistItemFromWatchlist(int $watchlistId)
    {
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_item_error']);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        $this->deleteItems($watchlistItems);
        
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_items']);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
        }
        
        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all_items_from_watchlist'], $watchlist->name);
        
        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }
    
    
    /**
     * delete complete watchlist
     *
     * @return string
     */
    public function deleteWatchlist(int $watchlistId)
    {
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all_error']);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        $this->deleteItems($watchlistItems);
        
        
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all_error']);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        $watchlist->delete();
        
        Session::getInstance()->set(WatchlistModel::WATCHLIST_SELECT, null);
        
        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all']);
        
        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }
    
    
    public function generateDownloadLink(WatchlistModel $watchlist, int $moduleId)
    {
        if (null === ($module = $this->framework->getAdapter(ModuleModel::class)->findByPk($moduleId))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_download_link_error'];
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        if (null === ($page = $this->framework->getAdapter(PageModel::class)->findByPk($module->downloadLink))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_download_link_page_error'];
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        // start lifecylce of download link when it is generated
        $watchlist->startedShare = strtotime('today');
        $watchlist->save();
        
        return $page->getAbsoluteUrl('?watchlist=' . $watchlist->uuid);
    }
    
    
    /**
     * @param WatchlistModel $watchlist
     *
     * @return string|boolean
     */
    public function downloadAll(WatchlistModel $watchlist)
    {
        /** @var $objPage \Contao\PageModel */
        global $objPage;
        
        if (null === ($items = $this->framework->getAdapter(WatchlistItemModel::class)->findPublishedByPid($watchlist->id))) {
            return false;
        }
        
        $basePath = $objPage->getFrontendUrl();
        $path     = 'files/tmp/download_' . $watchlist->hash . '.zip';
        
        $zipWriter = new ZipWriter($path);
        
        
        while ($items->next()) {
            if (WatchlistItemModel::WATCHLIST_ITEM_TYPE_DOWNLOAD != $items->type) {
                continue;
            }
            
            $zipWriter = $this->generateArchiveOutput($items, $zipWriter);
        }
        
        $zipWriter->close();
        
        // Open the "save as …" dialogue
        $file = new File($path, true);
        
        return $basePath . '?file=' . $path;
    }
    
    /**
     * adds file to zip
     *
     * @param WatchlistItemModel $item
     * @param ZipWriter          $zipWriter
     *
     * @return ZipWriter
     */
    protected function generateArchiveOutput(WatchlistItemModel $item, ZipWriter $zipWriter)
    {
        if (null === ($file = $this->framework->getAdapter(FilesModel::class)->findById($item->uuid))) {
            return $zipWriter;
        }
        
        $zipWriter->addFile($file->path, $file->name);
        
        return $zipWriter;
    }
    
    
    /**
     * save the active watchlist to the session
     */
    protected function setNewActiveWatchlist()
    {
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedByPid(FrontendUser::getInstance()->id))) {
            Session::getInstance()->set(WatchlistModel::WATCHLIST_SELECT, null);
        }
        
        Session::getInstance()->set(WatchlistModel::WATCHLIST_SELECT, $watchlist->id);
    }
    
    /**
     * get the status message
     *
     * @param string $message
     * @param string $status
     *
     * @return string
     */
    protected function getStatusMessage(string $message, string $status)
    {
        $template           = new FrontendTemplate('watchlist_message');
        $template->message  = $message;
        $template->cssClass = $status;
        
        return $template->parse();
    }
    
    /**
     * create a new watchlist
     *
     * @param string      $name
     * @param string|null $hash
     * @param null        $durability
     *
     * @return WatchlistModel
     */
    public function createWatchlist(string $name, string $hash = null, $durability = null)
    {
        if (null === ($userId = $this->framework->getAdapter(FrontendUser::class)->getInstance()->id)) {
            $userId = 0;
        }
        
        $watchlist            = new WatchlistModel();
        $watchlist->pid       = $userId;
        $watchlist->name      = $name;
    
        $watchlist->uuid      = $this->framework->getAdapter(StringUtil::class)->binToUuid($this->framework->getAdapter(Database::class)->getInstance()->getUuid());
        $watchlist->ip        = (!$this->framework->getAdapter(Config::class)->get('disableIpCheck') ? $this->framework->getAdapter(Environment::class)->get('ip') : '');
        $watchlist->sessionID = session_id();
        $watchlist->tstamp    = time();
        $watchlist->published = 1;
        $watchlist->hash      = isset($hash) ? $hash : sha1(session_id() . $watchlist->ip . $name);
        
        
        if ($durability == $GLOBALS['TL_LANG']['WATCHLIST']['durability']['default']) {
            $watchlist->start = strtotime('today');
            //add 29 days to timestamp to receive a different of 30 days
            $watchlist->stop = strtotime('tomorrow') + 60 * 60 * 24 * 29;
        }
        
        return $watchlist->save();
    }
    
    /**
     * add a item to a watchlist
     *
     * @param $id
     * @param $cid
     * @param $type
     * @param $pageID
     * @param $title
     * @param $watchlist
     *
     * @return string
     * @throws \Exception
     */
    protected function addItemToWatchlist($id, $cid, $type, $pageID, $title, $watchlist)
    {
        // Throw an exception if there's no id:
        if (!$this->framework->getAdapter(Validator::class)->isStringUuid($id)) {
            throw new \Exception('The watchlist requires items with an unique file uuid.');
        }
        
        if (null !== ($watchlistItem = $this->framework->getAdapter(WatchlistItemModel::class)->findByUuid($id))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_in_watchlist'], $watchlistItem->title, $watchlist->name);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        $item         = new WatchlistItemModel();
        $item->pid    = $watchlist->id;
        $item->uuid   = $this->framework->getAdapter(StringUtil::class)->uuidToBin($id); // transform string to bin
        $item->pageID = $pageID;
        $item->cid    = $cid;
        $item->type   = $type;
        $item->tstamp = time();
        $item->title  = $title;
        $item->save();
        
        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_add_item'], $item->title);
        
        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }
    
    protected function deleteItems($items)
    {
        while ($items->next()) {
            $items->delete();
        }
    }
}