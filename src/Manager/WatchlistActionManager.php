<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Contao\ZipWriter;
use HeimrichHannot\Ajax\Response\ResponseError;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Model\Collection;

class WatchlistActionManager
{
    const MESSAGE_STATUS_ERROR = 'watchlist-message-error';
    const MESSAGE_STATUS_SUCCESS = 'watchlist-message-success';

    /**
     * for tracking iterations.
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
     * delete a watchlist.
     *
     * @param int $id
     *
     * @return string
     */
    public function deleteWatchlistItem(int $id)
    {
        if (null === ($watchlistItem = $this->framework->getAdapter(WatchlistItemModel::class)->findInstanceByPk($id))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_item_error']);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_item'], $watchlistItem->title);
        $watchlistItem->delete();

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * delete all items from specific watchlist.
     *
     * @param int $watchlistId
     *
     * @return string
     */
    public function deleteWatchlistItemFromWatchlist(int $watchlistId)
    {
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_item_error']);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        $this->deleteItems($watchlistItems);

        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_items']);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
        }

        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_items_from_watchlist'], $watchlist->name);

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * @param int $watchlistId
     *
     * @return string
     */
    public function emptyWatchlist(int $watchlistId)
    {
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_error']);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        foreach ($watchlistItems as $item) {
            $item->delete();
        }

        $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_empty_watchlist'];

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * delete watchlist and its watchlistItems.
     *
     * @param int $watchlistId
     *
     * @return string
     */
    public function deleteWatchlist(int $watchlistId)
    {
        if (null !== ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlistId))) {
            // delete all items from watchlist
            foreach ($watchlistItems as $item) {
                $item->delete();
            }
        }

        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all_error']);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        // delete the watchlist
        $watchlist->delete();

        Session::getInstance()->set(WatchlistModel::WATCHLIST_SELECT, null);

        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_delete_all']);

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * generate link to public list of watchlist items.
     *
     * @param int $watchlistId
     * @param int $moduleId
     *
     * @return array
     */
    public function generateDownloadLink(int $watchlistId, int $moduleId)
    {
        if (null === ($module = $this->framework->getAdapter(ModuleModel::class)->findByPk($moduleId))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_download_link_error'];

            return [false, $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR)];
        }

        if (null === ($page = $this->framework->getAdapter(PageModel::class)->findByPk($module->downloadLink))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_download_link_page_error'];

            return [false, $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR)];
        }

        if (null === ($watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistModel(null, $watchlistId))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_no_watchlist_found'];

            return [false, $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR)];
        }

        // start lifecylce of download link when it is generated
        $watchlist->startShare = strtotime('today');
        $watchlist->save();

        return [urldecode($page->getAbsoluteUrl('?watchlist='.$watchlist->uuid)), false];
    }

    /**
     * get the status message.
     *
     * @param string $message
     * @param string $status
     *
     * @return string
     */
    public function getStatusMessage(string $message, string $status)
    {
        $template = new FrontendTemplate('watchlist_message');
        $template->message = $message;
        $template->cssClass = $status;

        return $template->parse();
    }

    /**
     * create a new watchlist.
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

        $watchlist = new WatchlistModel();
        $watchlist->pid = $userId;
        $watchlist->name = $name;

        $watchlist->uuid = $this->framework->getAdapter(StringUtil::class)->binToUuid($this->framework->getAdapter(Database::class)->getInstance()->getUuid());
        $watchlist->ip = (!$this->framework->getAdapter(Config::class)->get('disableIpCheck') ? $this->framework->getAdapter(Environment::class)->get('ip') : '');
        $watchlist->sessionID = session_id();
        $watchlist->tstamp = time();
        $watchlist->published = 1;
        $watchlist->hash = isset($hash) ? $hash : sha1(session_id().$watchlist->ip.$name);

        if ($GLOBALS['TL_LANG']['WATCHLIST']['durability']['default'] == $durability) {
            $watchlist->start = strtotime('today');
            //add 29 days to timestamp to receive a different of 30 days
            $watchlist->stop = strtotime('tomorrow') + 60 * 60 * 24 * 29;
        }

        return $watchlist->save();
    }

    /**
     * add a item to a watchlist.
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
        if (!isset($itemData['uuid']) && !isset($itemData['ptable']) && !isset($itemData['ptableId'])) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_no_data'];

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        // check if watchlist to which the item should be added exists
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_no_watchlist_found'];

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        // check if item is already in this watchlist
        if (isset($itemData['uuid']) && true !== ($response = $this->checkFile($watchlist, $itemData['uuid']))) {
            return $response;
        }

        if (isset($itemData['ptable']) && isset($itemData['ptableId'])
            && true !== ($response = $this->checkEntity($watchlist, $itemData['ptable'], $itemData['ptableId']))) {
            return $response;
        }

        global $objPage;

        $item = new WatchlistItemModel();
        $item->pid = $watchlist->id;
        $item->pageID = $objPage->id;
        $item->type = $type;
        $item->tstamp = time();

        $item->title = $itemData['title'] ? html_entity_decode($itemData['title']) : '';
        $item->uuid = $itemData['uuid'] ? StringUtil::uuidToBin(is_array($itemData['uuid']) ? $itemData['uuid']['uuid'] : $itemData['uuid']) : null;
        $item->ptable = $itemData['ptable'] ? $itemData['ptable'] : '';
        $item->ptableId = $itemData['ptableId'] ? $itemData['ptableId'] : '';
        $item->downloadable = $itemData['downloadable'];

        $item->save();

        $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_add_item'], $item->title);

        return $this->getStatusMessage($message, static::MESSAGE_STATUS_SUCCESS);
    }

    /**
     * create download zip file.
     *
     * @param int $watchlistId
     * @param int $moduleId
     *
     * @return ResponseError|string
     */
    public function getDownloadZip(int $watchlistId, int $moduleId)
    {
        if (null === ($items = System::getContainer()->get('huh.watchlist.watchlist_manager')->getItemsFromWatchlist($watchlistId))) {
            return new ResponseError();
        }

        if (null === ($module = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module', $moduleId))) {
            return new ResponseError();
        }

        $watchlistName = AjaxManager::XHR_GROUP;

        if (null !== ($watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistModel(null, $watchlistId))) {
            $watchlistName = (WatchlistManager::WATCHLIST_SESSION_BE == $watchlist->name
                              || WatchlistManager::WATCHLIST_SESSION_FE == $watchlist->name) ? $watchlistName : $watchlist->name;
        }

        if (1 == count($items)) {
            return System::getContainer()->get('huh.utils.file')->getPathFromUuid($items[0]->uuid);
        }

        return $this->createDownloadZipFile($items, $module, $watchlistName);
    }

    protected function createDownloadZipFile(Collection $items, ModuleModel $module, string $watchlistName)
    {
        $fileName = 'files/tmp/download_'.$watchlistName.'.zip';

        $zipWriter = new ZipWriter($fileName);

        foreach ($items as $item) {
            if (!$item->uuid && !$item->parentTable && !$item->parentTableId) {
                continue;
            }

            $class = '';
            switch ($item->type) {
                case WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE:
                    $class = System::getContainer()->get('huh.watchlist.watchlist_manager')->getClassByName($module->downloadItemFile, WatchlistManager::WATCHLIST_DOWNLOAD_FILE_GROUP);
                    break;
                case WatchlistItemModel::WATCHLIST_ITEM_TYPE_ENTITY:
                    $class = System::getContainer()->get('huh.watchlist.watchlist_manager')->getClassByName($module->downloadItemEntity, WatchlistManager::WATCHLIST_DOWNLOAD_ENTITY_GROUP);
            }

            if ('' == $class) {
                continue;
            }

            if (null === ($downloadItem = new $class($item->row()))) {
                continue;
            }

            if (null === ($download = $downloadItem->retrieveItem())) {
                continue;
            }

            $zipWriter->addFile($download->getFile(), $download->getTitle());
        }

        $zipWriter->close();

        return $fileName;
    }

    /**
     * check if uuid is valid and if file is already in watchlist.
     *
     * @param $watchlist
     * @param $uuid
     *
     * @return bool|string
     */
    protected function checkFile($watchlist, $uuid)
    {
        if (is_array($uuid)) {
            $uuid = $uuid['uuid'];
        }

        if (!Validator::isStringUuid($uuid)) {
            $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_invalid_file'];

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        if (false !== ($watchlistItem = System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, $uuid))) {
            if (WatchlistManager::WATCHLIST_SESSION_BE == $watchlist->name || WatchlistManager::WATCHLIST_SESSION_FE == $watchlist->name) {
                $message = $GLOBALS['TL_LANG']['WATCHLIST']['message_in_watchlist_general'];
            } else {
                $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_in_watchlist'], $watchlist->name);
            }

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        return true;
    }

    /**
     * check if file is already in watchlist.
     *
     * @param $watchlist
     * @param $ptable
     * @param $ptableId
     *
     * @return bool|string
     */
    protected function checkEntity($watchlist, $ptable, $ptableId)
    {
        if (null !== ($watchlistItem = System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($watchlist->id, null, $ptable, $ptableId))) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['notify_in_watchlist'], $watchlistItem->title, $watchlist->name);

            return $this->getStatusMessage($message, static::MESSAGE_STATUS_ERROR);
        }

        return true;
    }
}
