<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\AjaxBundle\Response\ResponseData;
use HeimrichHannot\AjaxBundle\Response\ResponseError;
use HeimrichHannot\AjaxBundle\Response\ResponseSuccess;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;

class AjaxManager
{
    const XHR_GROUP = 'watchlist';

    const XHR_PARAMETER_MODULE_ID = 'moduleId';
    const XHR_PARAMETER_WATCHLIST_ITEM_ID = 'itemId';
    const XHR_PARAMETER_WATCHLIST_ITEM_UUID = 'uuid';
    const XHR_PARAMETER_WATCHLIST_ITEM_TITLE = 'title';
    const XHR_PARAMETER_WATCHLIST_ITEM_DATA_CONTAINER = 'dataContainer';
    const XHR_PARAMETER_WATCHLIST_ITEM_PAGE = 'pageID';
    const XHR_PARAMETER_WATCHLIST_ITEM_DATA = 'itemData';
    const XHR_PARAMETER_WATCHLIST_ITEM_TYPE = 'type';
    const XHR_PARAMETER_WATCHLIST_NAME = 'watchlist';
    const XHR_PARAMETER_WATCHLIST_DURABILITY = 'durability';
    const XHR_PARAMETER_WATCHLIST_ITEM_OPTIONS = 'options';
    const XHR_PARAMETER_WATCHLIST_WATCHLIST_ID = 'watchlistId';
    const XHR_WATCHLIST_ADD_ACTION = 'watchlistAddAction';
    const XHR_WATCHLIST_NEW_WATCHLIST_ADD_ITEM_ACTION = 'watchlistNewWatchlistAddAction';
    const XHR_WATCHLIST_DELETE_ITEM_ACTION = 'watchlistDeleteItemAction';
    const XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION = 'watchlistEmptyWatchlistAction';
    const XHR_WATCHLIST_DELETE_WATCHLIST_ACTION = 'watchlistDeleteWatchlistAction';
    const XHR_WATCHLIST_SELECT_ACTION = 'watchlistSelectAction';
    const XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION = 'watchlistUpdateModalAddAction';
    const XHR_WATCHLIST_DOWNLOAD_LINK_ACTION = 'watchlistGenerateDownloadLinkAction';
    const XHR_WATCHLIST_DOWNLOAD_ALL_ACTION = 'watchlistDownloadAllAction';
    const XHR_WATCHLIST_SHOW_MODAL_ACTION = 'watchlistShowModalAction';
    const XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION = 'watchlistShowModalAddAction';
    const XHR_WATCHLIST_UPDATE_WATCHLIST_ACTION = 'watchlistUpdateWatchlistAction';
    const XHR_WATCHLIST_ADD_ITEM_TO_SELECTED_WATCHLIST = 'watchlistAddItemToSelectedWatchlistAction';
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var WatchlistActionManager
     */
    protected $actionManager;

    /**
     * @var WatchlistManager
     */
    protected $watchlistManager;

    /**
     * @var WatchlistTemplateManager
     */
    protected $watchlistTemplate;

    public function __construct(
        ContaoFrameworkInterface $framework,
        WatchlistTemplateManager $watchlistTemplate,
        WatchlistActionManager $actionManager,
        WatchlistManager $watchlistManager
    ) {
        $this->framework = $framework;
        $this->watchlistTemplate = $watchlistTemplate;
        $this->actionManager = $actionManager;
        $this->watchlistManager = $watchlistManager;
    }

    public function ajaxActions()
    {
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_SHOW_MODAL_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_ADD_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_ITEM_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_WATCHLIST_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_NEW_WATCHLIST_ADD_ITEM_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_UPDATE_WATCHLIST_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_ADD_ITEM_TO_SELECTED_WATCHLIST, $this);
    }

    /**
     * @param $moduleId
     * @param $watchlistId
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistShowModalAction($moduleId, $watchlistId = null)
    {
        if (!$moduleId && !$watchlistId) {
            return new ResponseError();
        }

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['response' => $this->watchlistTemplate->getWatchlistModal($moduleId, $watchlistId)]));

        return $response;
    }

    /**
     * clicked on the add to watchlist button.
     *
     * @param      $moduleId
     * @param      $type
     * @param null $itemData
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistAddAction($moduleId, $type, $itemData = null)
    {
        if (FE_USER_LOGGED_IN) {
            return $this->watchlistShowModalAddAction($moduleId, $type, $itemData);
        }

        if (isset($itemData['options']) && count($itemData['options']) > 1) {
            $responseContent = $this->watchlistTemplate->getWatchlistItemOptions($moduleId, $type, $itemData['options']);

            return $this->getModalResponse($responseContent);
        }

        if (!isset($itemData['uuid'])) {
            return new ResponseError();
        }

        return $this->addItemToWatchlist(Session::getInstance()->get(WatchlistModel::WATCHLIST_SELECT), $type, $itemData);
    }

    /**
     * add item to watchlist that has been selected by user.
     *
     * @param int    $watchlistId
     * @param string $type
     * @param        $item
     *
     * @return ResponseSuccess
     */
    public function watchlistAddItemToSelectedWatchlistAction(int $watchlistId, $type, $item)
    {
        $message = $this->actionManager->addItemToWatchlist($watchlistId, $type, $item);

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['message' => $message]));

        return $response;
    }

    /**
     * create new watchlist and add item to it.
     *
     * @param int    $moduleId
     * @param        $itemData
     * @param string $name
     * @param string $type
     * @param null   $durability
     *
     * @return ResponseSuccess
     */
    public function watchlistNewWatchlistAddAction(int $moduleId, $itemData, string $name, string $type, $durability = null)
    {
        $response = new ResponseSuccess();

        if (null !== $this->watchlistManager->getWatchlistByName($name)) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_already_exists'], $name);
            $response->setResult(new ResponseData('', ['message' => $this->actionManager->getStatusMessage($message, WatchlistActionManager::MESSAGE_STATUS_ERROR), null, 0]));

            return $response;
        }

        $watchlist = System::getContainer()->get('huh.watchlist.action_manager')->createWatchlist($name);

        if (!is_array($itemData)) {
            $data = json_decode($itemData);
            $itemData = [
                'uuid' => $data->uuid,
                'title' => $data->title,
            ];
        }

        $message = System::getContainer()->get('huh.watchlist.action_manager')->addItemToWatchlist($watchlist->id, $type, $itemData);

        $response->setResult(new ResponseData('', ['message' => $message, null, 1]));

        return $response;
    }

    /**
     * update watchlist.
     *
     * @param int $moduleId
     * @param int $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistUpdateWatchlistAction(int $moduleId, int $watchlistId)
    {
        list($watchlist, $title, $count) = $this->watchlistTemplate->getUpdatedWatchlist($moduleId, $watchlistId);

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['watchlist' => $watchlist, 'modalTitle' => $title, 'count' => $count]));

        return $response;
    }

    /**
     * delete specific item from watchlist and update the watchlist.
     *
     * @param int $itemId
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistDeleteItemAction($moduleId, int $itemId)
    {
        if (null === ($watchlistId = System::getContainer()->get('huh.watchlist.watchlist_item_manager')->getWatchlistIdFromItem($itemId))) {
            return new ResponseError();
        }

        $message = $this->actionManager->deleteWatchlistItem($itemId);
        list($updatedWatchlist, $title, $count) = $this->watchlistTemplate->getUpdatedWatchlist($moduleId, $watchlistId);

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['message' => $message, 'watchlist' => $updatedWatchlist, 'modalTitle' => $title, 'count' => $count]));

        return $response;
    }

    /**
     * delete all items from specific watchlist.
     *
     * @param int $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistEmptyWatchlistAction(int $moduleId, int $watchlistId)
    {
        $response = new ResponseSuccess();

        $message = $this->actionManager->emptyWatchlist($watchlistId);
        list($updatedWatchlist, $title, $count) = $this->watchlistTemplate->getUpdatedWatchlist($moduleId, $watchlistId);

        $response->setResult(new ResponseData('', ['message' => $message, 'watchlist' => $updatedWatchlist, 'modalTitle' => $title, 'count' => $count]));

        return $response;
    }

    /**
     * delete specific watchlist.
     *
     * @param int $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistDeleteWatchlistAction(int $moduleId, int $watchlistId)
    {
        $response = new ResponseSuccess();

        $message = $this->actionManager->deleteWatchlist($watchlistId);
        $user = FrontendUser::getInstance();
        list($updatedWatchlist, $title, $count) = $this->watchlistTemplate->getUpdatedWatchlist($moduleId, $user->id);

        $response->setResult(new ResponseData('', ['message' => $message, 'watchlist' => $updatedWatchlist, 'modalTitle' => $title, 'count' => $count]));

        return $response;
    }

    /**
     * download all elements of current watchlist as zip file.
     *
     * @param $watchlistId
     * @param $moduleId
     *
     * @return ResponseSuccess
     */
    public function watchlistDownloadAllAction($watchlistId, $moduleId)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['file' => $this->actionManager->getDownloadZip($watchlistId, $moduleId)]));

        return $response;
    }

    /**
     * get watchlist modal.
     *
     * @param $content
     *
     * @return ResponseSuccess
     */
    public function getModalResponse($content)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['response' => $this->watchlistTemplate->getModal($content)]));

        return $response;
    }

    /**
     * check if a entity has options.
     *
     * @param int    $id
     * @param int    $moduleId
     * @param string $dataContainer
     *
     * @return array|bool
     */
    public function checkForOptions(int $id, int $moduleId, string $dataContainer)
    {
        if (null === ($module = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module', $moduleId))) {
            return false;
        }

        if (null === ($item = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dataContainer, $id))) {
            return false;
        }

        $options = [];

        foreach (StringUtil::deserialize($module->fileFieldEntity, true) as $field) {
            $options[] = $this->checkEntityField($item, $dataContainer, $field);
        }

        foreach (StringUtil::deserialize($module->fileFieldChildEntity, true) as $field) {
            $options[] = $this->checkEntityField($item, $dataContainer, $field);
        }

        if (!empty($options)) {
            return $options;
        }

        return false;
    }

    /**
     * show the add action modal.
     *
     * @param int $moduleId
     * @param     $itemData
     *
     * @return ResponseSuccess
     */
    //int $id, int $cid, $type, int $pageID, string $title
    public function watchlistShowModalAddAction(int $moduleId, string $type, $itemData)
    {
        $response = new ResponseSuccess();

        list($message, $modal, $count) = $this->watchlistTemplate->getWatchlistAddModal($moduleId, $type, $itemData);

        $response->setResult(new ResponseData('', ['message' => $message, 'modal' => $modal, 'count' => $count]));

        return $response;
    }

    /**
     * get the link to the public download list of the current watchlist
     * this link can be shared so that other users can see the items of the watchlist.
     *
     * @param int      $moduleId
     * @param int|null $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistGenerateDownloadLinkAction(int $moduleId, int $watchlistId = null)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData(false));

        if (!isset($watchlistId)) {
            $watchlistId = Session::getInstance()->get(WatchlistModel::WATCHLIST_SELECT);
        }

        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            return $response;
        }

        list($link, $message) = $this->actionManager->generateDownloadLink($watchlistId, $moduleId);

        $response->setResult(new ResponseData('', ['link' => $link, 'message' => $message]));

        return $response;
    }

    /**
     * add the item to a watchlist.
     *
     * @param $watchlistId
     * @param $type
     * @param $itemData
     *
     * @return ResponseError|ResponseSuccess
     */
    public function addItemToWatchlist($watchlistId, $type, $itemData)
    {
        $response = new ResponseSuccess();

        if (null === ($responseData = System::getContainer()->get('huh.watchlist.action_manager')->addItemToWatchlist($watchlistId, $type, $itemData))) {
            return new ResponseError();
        }

        $count = 0;
        if (null !== ($watchlistItems = System::getContainer()->get('huh.watchlist.watchlist_manager')->getItemsFromWatchlist($watchlistId))) {
            $count = $watchlistItems->count();
        }

        $response->setResult(new ResponseData('', ['message' => $responseData, 'count' => $count]));

        return $response;
    }

    /**
     * check if configured field exists.
     *
     * @param $item
     * @param $dataContainer
     * @param $field
     *
     * @return mixed|null
     */
    protected function checkEntityField($item, $dataContainer, $field)
    {
        if (isset($item->{$field})) {
            if (null !== ($items = $this->framework->getAdapter(FilesModel::class)->findMultipleByUuids(StringUtil::deserialize($item->{$field})))) {
                return $items;
            }
        }

        if (isset($GLOBALS['TL_DCA'][$dataContainer]['config']['ctable'])) {
            if (null !== ($cItems = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dataContainer, $item->id))) {
                return $cItems;
            }
        }

        return null;
    }
}
