<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\StringUtil;
use HeimrichHannot\AjaxBundle\Response\ResponseData;
use HeimrichHannot\AjaxBundle\Response\ResponseError;
use HeimrichHannot\AjaxBundle\Response\ResponseSuccess;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AjaxManager
{
    const XHR_GROUP = 'watchlist';

    const XHR_PARAMETER_DATA                               = 'data';
    const XHR_PARAMETER_MODULE_ID                          = 'moduleId';
    const XHR_PARAMETER_WATCHLIST_ITEM_ID                  = 'itemId';
    const XHR_PARAMETER_WATCHLIST_ITEM_UUID                = 'uuid';
    const XHR_PARAMETER_WATCHLIST_ITEM_TITLE               = 'title';
    const XHR_PARAMETER_WATCHLIST_ITEM_DATA_CONTAINER      = 'dataContainer';
    const XHR_PARAMETER_WATCHLIST_ITEM_PAGE                = 'pageID';
    const XHR_PARAMETER_WATCHLIST_ITEM_DATA                = 'itemData';
    const XHR_PARAMETER_WATCHLIST_ITEM_TYPE                = 'type';
    const XHR_PARAMETER_WATCHLIST_NAME                     = 'watchlist';
    const XHR_PARAMETER_WATCHLIST_DURABILITY               = 'durability';
    const XHR_PARAMETER_WATCHLIST_ITEM_OPTIONS             = 'options';
    const XHR_PARAMETER_WATCHLIST_WATCHLIST_ID             = 'watchlistId';
    const XHR_WATCHLIST_ADD_ACTION                         = 'watchlistAddAction';
    const XHR_WATCHLIST_NEW_WATCHLIST_ADD_ITEM_ACTION      = 'watchlistNewWatchlistAddAction';
    const XHR_WATCHLIST_DELETE_ITEM_ACTION                 = 'watchlistDeleteItemAction';
    const XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION             = 'watchlistEmptyWatchlistAction';
    const XHR_WATCHLIST_DELETE_WATCHLIST_ACTION            = 'watchlistDeleteWatchlistAction';
    const XHR_WATCHLIST_SELECT_ACTION                      = 'watchlistSelectAction';
    const XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION            = 'watchlistUpdateModalAddAction';
    const XHR_WATCHLIST_DOWNLOAD_LINK_ACTION               = 'watchlistGenerateDownloadLinkAction';
    const XHR_WATCHLIST_DOWNLOAD_ALL_ACTION                = 'watchlistDownloadAllAction';
    const XHR_WATCHLIST_SHOW_MODAL_ACTION                  = 'watchlistShowModalAction';
    const XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION              = 'watchlistShowModalAddAction';
    const XHR_WATCHLIST_UPDATE_WATCHLIST_ACTION            = 'watchlistUpdateWatchlistAction';
    const XHR_WATCHLIST_ADD_ITEM_TO_SELECTED_WATCHLIST     = 'watchlistAddItemToSelectedWatchlistAction';
    const XHR_WATCHLIST_SEND_DOWNLOAD_LINK_NOTIFICATION    = 'watchlistSendDownloadLinkNotification';
    const XHR_WATCHLIST_SEND_DOWNLOAD_LINK_AS_NOTIFICATION = 'watchlistSendDownloadLinkAsNotification';
    const XHR_WATCHLIST_LOAD_DOWNLOAD_LINK_FORM            = 'watchlistLoadDownloadLinkForm';

    /**
     * @var ContaoFramework
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
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        ContainerInterface $container,
        WatchlistTemplateManager $watchlistTemplate,
        WatchlistActionManager $actionManager,
        WatchlistManager $watchlistManager
    ) {
        $this->framework         = $container->get('contao.framework');
        $this->watchlistTemplate = $watchlistTemplate;
        $this->actionManager     = $actionManager;
        $this->watchlistManager  = $watchlistManager;
        $this->container = $container;
    }

    public function ajaxActions()
    {
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_SHOW_MODAL_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_ADD_ACTION,
            $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_DELETE_ITEM_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_DELETE_WATCHLIST_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_NEW_WATCHLIST_ADD_ITEM_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_UPDATE_WATCHLIST_ACTION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_ADD_ITEM_TO_SELECTED_WATCHLIST, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_SEND_DOWNLOAD_LINK_NOTIFICATION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_SEND_DOWNLOAD_LINK_AS_NOTIFICATION, $this);
        $this->container->get('huh.ajax')->runActiveAction(static::XHR_GROUP,
            static::XHR_WATCHLIST_LOAD_DOWNLOAD_LINK_FORM, $this);
    }

    /**
     * @param string $data
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistShowModalAction(string $data)
    {
        $data        = json_decode($data);
        $moduleId    = $data->moduleId;
        $watchlistId = $data->watchlistId;

        if (!$moduleId && !$watchlistId) {
            return new ResponseError();
        }

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('',
            ['response' => $this->watchlistTemplate->getWatchlistWindow($moduleId, $watchlistId)]));

        return $response;
    }

    /**
     * clicked on the add to watchlist button.
     *
     * @param string $data
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistAddAction(string $data)
    {
        $data     = json_decode($data);
        $moduleId = $data->moduleId;
        $type     = $data->type;
        $itemData = $data->itemData;

        if (FE_USER_LOGGED_IN) {
            return $this->watchlistShowModalAddAction($moduleId, $type, $itemData);
        }

        if (isset($itemData->options) && is_array($itemData->options) && count($itemData->options) > 1) {
            $responseContent = $this->watchlistTemplate->getWatchlistItemOptions($moduleId, $type, $itemData->options);

            return $this->getModalResponse($responseContent);
        }

        if (!isset($itemData->uuid)) {
            return new ResponseError();
        }

        return $this->addItemToWatchlist($this->container->get('session')->get(WatchlistModel::WATCHLIST_SELECT), $type,
            $itemData);
    }

    /**
     * add item to watchlist that has been selected by user.
     *
     * @param string $data
     *
     * @return ResponseSuccess
     */
    public function watchlistAddItemToSelectedWatchlistAction(string $data)
    {
        $data        = json_decode($data);
        $watchlistId = $data->watchlistId;
        $type        = $data->type;
        $item        = $data->item;

        $message = $this->actionManager->addItemToWatchlist($watchlistId, $type, $item);

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['message' => $message]));

        return $response;
    }

    /**
     * create new watchlist and add item to it.
     *
     * @param string $data
     *
     * @return ResponseSuccess
     */
    public function watchlistNewWatchlistAddAction(string $data)
    {
        $data       = json_decode($data);
        $moduleId   = $data->moduleId;
        $itemData   = $data->itemData;
        $name       = $data->name;
        $type       = $data->type;
        $durability = $data->durability ? $data->durability : null;

        $response = new ResponseSuccess();

        if (null !== $this->watchlistManager->getWatchlistByName($name)) {
            $message = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['message_watchlist_already_exists'], $name);
            $response->setResult(new ResponseData('',
                [
                    'message' => $this->actionManager->getStatusMessage($message,
                        WatchlistActionManager::MESSAGE_STATUS_ERROR),
                    null,
                    0
                ]));

            return $response;
        }

        $watchlist = $this->actionManager->createWatchlist($name);

        if (!is_array($itemData)) {
            $data     = null !== json_decode($itemData) ? json_decode($itemData) : $itemData;
            $itemData = [
                'uuid' => $data->uuid,
                'title' => $data->title,
            ];
        }

        $message = $this->actionManager->addItemToWatchlist($watchlist->id,
            $type, $itemData);

        $response->setResult(new ResponseData('', ['message' => $message, null, 1]));

        return $response;
    }

    /**
     * update watchlist.
     *
     * @param string $data
     *
     * @return ResponseSuccess
     */
    public function watchlistUpdateWatchlistAction(string $data)
    {
        $data        = json_decode($data);
        $moduleId    = $data->moduleId;
        $watchlistId = $data->watchlistId;

        list($watchlist, $title, $count) = $this->watchlistTemplate->getUpdatedWatchlist($moduleId, $watchlistId);

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('',
            ['watchlist' => $watchlist, 'modalTitle' => $title, 'count' => $count]));

        return $response;
    }

    /**
     * delete specific item from watchlist and update the watchlist.
     *
     * @param string $data
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistDeleteItemAction(string $data)
    {
        $data     = json_decode($data);
        $moduleId = $data->moduleId;
        $itemId   = $data->itemId;

        if (null === ($watchlistId = $this->container->get('huh.watchlist.watchlist_item_manager')->getWatchlistIdFromItem($itemId))) {
            return new ResponseError();
        }

        $message = $this->actionManager->deleteWatchlistItem($itemId);
        list($updatedWatchlist, $title, $count) = $this->watchlistTemplate->getUpdatedWatchlist($moduleId,
            $watchlistId);

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('',
            ['message' => $message, 'watchlist' => $updatedWatchlist, 'modalTitle' => $title, 'count' => $count]));

        return $response;
    }

    /**
     * delete all items from specific watchlist.
     *
     * @param string $data
     *
     * @return ResponseSuccess
     */
    public function watchlistEmptyWatchlistAction(string $data)
    {
        $data        = json_decode($data);
        $moduleId    = $data->moduleId;
        $watchlistId = $data->watchlistId;

        $response = new ResponseSuccess();

        $message = $this->actionManager->emptyWatchlist($watchlistId);
        list($updatedWatchlist, $title, $count) = $this->watchlistTemplate->getUpdatedWatchlist($moduleId,
            $watchlistId);

        $response->setResult(new ResponseData('',
            ['message' => $message, 'watchlist' => $updatedWatchlist, 'modalTitle' => $title, 'count' => $count]));

        return $response;
    }

    /**
     * delete specific watchlist.
     *
     * @param string $data
     *
     * @return ResponseSuccess
     */
    public function watchlistDeleteWatchlistAction(string $data)
    {
        $data        = json_decode($data);
        $moduleId    = $data->moduleId;
        $watchlistId = $data->watchlistId;

        $response = new ResponseSuccess();

        $message = $this->actionManager->deleteWatchlist($watchlistId);
//        $user = FrontendUser::getInstance();
        list($updatedWatchlist, $title, $count) = $this->watchlistTemplate->getUpdatedWatchlist($moduleId);

        $response->setResult(new ResponseData('',
            ['message' => $message, 'watchlist' => $updatedWatchlist, 'modalTitle' => $title, 'count' => $count]));

        return $response;
    }

    /**
     * download all elements of current watchlist as zip file.
     *
     * @param string $data
     *
     * @throws \Exception
     *
     * @return ResponseSuccess
     */
    public function watchlistDownloadAllAction(string $data)
    {
        $data        = json_decode($data);
        $moduleId    = $data->moduleId;
        $watchlistId = $data->watchlistId;

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('',
            ['file' => $this->actionManager->getDownloadZip($moduleId, $watchlistId)]));

        return $response;
    }

    public function watchlistSendDownloadLinkNotification(string $data)
    {
        $data = json_decode($data);

        $response = new ResponseSuccess();
        $message  = $this->actionManager->sendDownloadLinkNotification($data);
        $response->setResult(new ResponseData('', ['message' => $message]));

        return $response;
    }

    public function watchlistLoadDownloadLinkForm(string $data)
    {
        $data        = json_decode($data);
        $moduleId    = $data->moduleId;
        $watchlistId = $data->watchlistId;

        $response = new ResponseSuccess();
        $form     = $this->actionManager->watchlistLoadDownloadLinkForm($moduleId, $watchlistId);
        $response->setResult(new ResponseData('', ['form' => $form]));

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
        $response->setResult(new ResponseData('', ['response' => $this->watchlistTemplate->generateWatchlistWindow($content)]));

        return $response;
    }

    /**
     * check if a entity has options.
     *
     * @param int $id
     * @param int $moduleId
     * @param string $dataContainer
     *
     * @return array|bool
     */
    public function checkForOptions(int $id, int $moduleId, string $dataContainer)
    {
        if (null === ($module = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_module',
                $moduleId))) {
            return false;
        }

        if (null === ($item = $this->container->get('huh.utils.model')->findModelInstanceByPk($dataContainer,
                $id))) {
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
     * @param int $moduleId
     * @param int|null $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistGenerateDownloadLinkAction(int $moduleId, int $watchlistId = null)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData(false));

        if (!isset($watchlistId)) {
            $watchlistId = $this->container->get('session')->get(WatchlistModel::WATCHLIST_SELECT);
        }

        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            return $response;
        }

        list($link, $message) = $this->actionManager->generateDownloadLink($moduleId, $watchlistId);

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

        if (null === ($responseData = $this->actionManager->addItemToWatchlist($watchlistId,
                $type, $itemData))) {
            return new ResponseError();
        }

        $count = 0;
        if (null !== ($watchlistItems = $this->watchlistManager->getItemsFromWatchlist($watchlistId))) {
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
            if (null !== ($cItems = $this->container->get('huh.utils.model')->findModelInstanceByPk($dataContainer,
                    $item->id))) {
                return $cItems;
            }
        }

        return null;
    }
}
