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
use Contao\System;
use Contao\Validator;
use Contao\ZipWriter;
use function GuzzleHttp\Promise\promise_for;
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
     * delete a watchlist
     *
     * @param int $id
     *
     * @return string
     */
    public function deleteWatchlistItem(int $id)
    {
        if (null === ($watchlistItem = $this->framework->getAdapter(WatchlistItemModel::class)->findInstanceByPk($id))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_item_error']);
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
    
        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_item'], $watchlistItem->title);
        $watchlistItem->delete();
        
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
    
    
    public function emptyWatchlist(int $watchlistId)
    {
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_delete_all_error']);
        
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        foreach($watchlistItems as $item)
        {
            $item->delete();
        }
    
        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_empty_watchlist']);
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
        
        $watchlist       = new WatchlistModel();
        $watchlist->pid  = $userId;
        $watchlist->name = $name;
        
        $watchlist->uuid      =
            $this->framework->getAdapter(StringUtil::class)->binToUuid($this->framework->getAdapter(Database::class)->getInstance()->getUuid());
        $watchlist->ip        =
            (!$this->framework->getAdapter(Config::class)->get('disableIpCheck') ? $this->framework->getAdapter(Environment::class)->get('ip') : '');
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
     * @param int    $watchlistId
     * @param string $type
     * @param        $itemData
     *
     * @return string
     */
    public function addItemToWatchlist(int $watchlistId, string $type, $itemData)
    {
        // check if needed itemData was submitted (`uuid` for type `file`, `ptable` and `ptableId` for `entity`)
        if (!isset($itemData->uuid) && !isset($itemData->ptable) && !isset($itemData->ptableId)) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_no_data'];
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        // check if watchlist to which the item should be added exists
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_no_watchlist_found'];
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        
        // check if item is already in this watchlist
        if (isset($itemData->uuid) && true !== ($response = $this->checkFile($watchlist, $itemData->uuid))) {
            return $response;
        }
        
        if (isset($itemData->ptable) && isset($itemData->ptableId)
            && true !== ($response = $this->checkEntity($watchlist, $itemData->ptable, $itemData->ptableId))) {
            return $response;
        }
        
        global $objPage;
        
        $item         = new WatchlistItemModel();
        $item->pid    = $watchlist->id;
        $item->pageID = $objPage->id;
        $item->type   = $type;
        $item->tstamp = time();
        
        $item->title    = $itemData->title ? $itemData->title : '';
        $item->uuid     = $itemData->uuid ? StringUtil::uuidToBin($itemData->uuid) : null;
        $item->ptable   = $itemData->ptable? $itemData->ptable : '';
        $item->ptableId = $itemData->ptableId ? $itemData->ptableId : '';
        
        $item->save();
        
        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_add_item'], $item->title);
        
        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }
    
    /**
     * check if uuid is valid and if file is already in watchlist
     *
     * @param $watchlist
     * @param $uuid
     *
     * @return bool|string
     */
    protected function checkFile($watchlist, $uuid)
    {
        if (!Validator::isStringUuid($uuid)) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_invalid_file'];
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        if (false !== ($watchlistItem =
                System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, $uuid))) {
            
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_in_watchlist'], $watchlist->name);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        return true;
    }
    
    /**
     * check if file is already in watchlist
     *
     * @param $watchlist
     * @param $ptable
     * @param $ptableId
     *
     * @return bool|string
     */
    protected function checkEntity($watchlist, $ptable, $ptableId)
    {
        if (null !== ($watchlistItem =
                System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, null, $ptable, $ptableId))) {
            
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_in_watchlist'], $watchlistItem->title, $watchlist->name);
            
            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }
        
        return true;
    }
    
    
    protected function deleteItems($items)
    {
        while ($items->next()) {
            $items->delete();
        }
    }
}