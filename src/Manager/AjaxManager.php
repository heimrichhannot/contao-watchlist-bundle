<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.03.18
 * Time: 08:45
 */

namespace HeimrichHannot\WatchlistBundle\Manager;


use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\File;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Contao\ZipWriter;
use HeimrichHannot\AjaxBundle\Response\ResponseData;
use HeimrichHannot\AjaxBundle\Response\ResponseError;
use HeimrichHannot\AjaxBundle\Response\ResponseSuccess;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;

class AjaxManager
{
    const XHR_GROUP = 'watchlist';
    
    const XHR_PARAMETER_MODULE_ID                     = 'moduleId';
    const XHR_PARAMETER_WATCHLIST_ITEM_ID             = 'itemId';
    const XHR_PARAMETER_WATCHLIST_ITEM_UUID           = 'uuid';
    const XHR_PARAMETER_WATCHLIST_ITEM_TITLE          = 'title';
    const XHR_PARAMETER_WATCHLIST_ITEM_DATA_CONTAINER = 'dataContainer';
    const XHR_PARAMETER_WATCHLIST_ITEM_PAGE           = 'pageID';
    const XHR_PARAMETER_WATCHLIST_ITEM_TYPE           = 'type';
    const XHR_PARAMETER_WATCHLIST_NAME                = 'watchlist';
    const XHR_PARAMETER_WATCHLIST_DURABILITY          = 'durability';
    const XHR_PARAMETER_WATCHLIST_ITEM_OPTIONS        = 'options';
    const XHR_PARAMETER_WATCHLIST_WATCHLIST_ID        = 'watchlistId';
    const XHR_WATCHLIST_ADD_ACTION                    = 'watchlistAddAction';
    const XHR_WATCHLIST_DELETE_ITEM_ACTION            = 'watchlistDeleteItemAction';
    const XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION        = 'watchlistEmptyWatchlistAction';
    const XHR_WATCHLIST_DELETE_WATCHLIST_ACTION       = 'watchlistDeleteWatchlistAction';
    const XHR_WATCHLIST_UPDATE_ACTION                 = 'watchlistUpdateAction';
    const XHR_WATCHLIST_SELECT_ACTION                 = 'watchlistSelectAction';
    const XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION       = 'watchlistUpdateModalAddAction';
    const XHR_WATCHLIST_DOWNLOAD_LINK_ACTION          = 'watchlistGenerateDownloadLinkAction';
    const XHR_WATCHLIST_DOWNLOAD_ALL_ACTION           = 'watchlistDownloadAllAction';
    const XHR_WATCHLIST_MULTIPLE_ADD_ACTION           = 'watchlistMultipleAddAction';
    const XHR_WATCHLIST_MULTIPLE_SELECT_ADD_ACTION    = 'watchlistMultipleSelectAddAction';
    const XHR_WATCHLIST_SHOW_MODAL_ACTION             = 'watchlistShowModalAction';
    const XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION         = 'watchlistShowModalAddAction';
    
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
        $this->framework         = $framework;
        $this->watchlistTemplate = $watchlistTemplate;
        $this->actionManager     = $actionManager;
        $this->watchlistManager  = $watchlistManager;
        
        
    }
    
    public function ajaxActions()
    {
        $this->addAjaxActions();
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_SHOW_MODAL_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_ADD_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_ITEM_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_WATCHLIST_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION, $this);
    }
    
    protected function addAjaxActions()
    {
        $GLOBALS['AJAX'][static::XHR_GROUP] = [
            'actions' => [
                static::XHR_WATCHLIST_SHOW_MODAL_ACTION       => [
                    'arguments' => [
                        static::XHR_PARAMETER_MODULE_ID,
                        static::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_ADD_ACTION              => [
                    'arguments' => [
                        static::XHR_PARAMETER_MODULE_ID,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_OPTIONS,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_UUID
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION   => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_UPDATE_ACTION           => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION    => [
                    'arguments' => [
                        static::XHR_PARAMETER_MODULE_ID,
                        static::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION     => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_DELETE_ITEM_ACTION      => [
                    'arguments' => [
                        static::XHR_PARAMETER_MODULE_ID,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION  => [
                    'arguments' => [
                        static::XHR_PARAMETER_MODULE_ID,
                        static::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_DELETE_WATCHLIST_ACTION => [
                    'arguments' => [
                        static::XHR_PARAMETER_MODULE_ID,
                        static::XHR_PARAMETER_WATCHLIST_WATCHLIST_ID
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_SELECT_ACTION           => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ]
            ],
        ];
    }
    
    /**
     * @param $moduleId
     * @param $watchlistId
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistShowModalAction($moduleId, $watchlistId = null)
    {
        if (null === $moduleId && null === $watchlistId) {
            return new ResponseError();
        }
        
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['response' => $this->getWatchlistModal($moduleId, $watchlistId)]));
        
        return $response;
    }
    
    /**
     * clicked on the add to watchlist button
     *
     * @param      $moduleId
     * @param      $type
     * @param null $options
     * @param null $itemData
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistAddAction($moduleId, $type, $options = null, $itemData = null)
    {
        if (null !== $options && count($options) > 1) {
            return $this->getWatchlistItemOptionsModal($moduleId, $type, $options);
        }
        
        if (null === $itemData) {
            return new ResponseError();
        }
        
        if (FE_USER_LOGGED_IN) {
            return $this->watchlistShowModalAddAction($moduleId, $itemData);
        }
        
        return $this->addItemToWatchlist(Session::getInstance()->get(WatchlistModel::WATCHLIST_SELECT), $type, $itemData);
    }
    
    
    /**
     * delete specific item from watchlist and update the watchlist
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
        list($updatedWatchlist, $count) = $this->getUpdatedWatchlist($moduleId, $watchlistId);
        
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['message' => $message, 'watchlist' => $updatedWatchlist, 'count' => $count]));
        
        return $response;
    }
    
    
    /**
     * delete all items from specific watchlist
     *
     * @param int $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistEmptyWatchlistAction(int $moduleId, int $watchlistId)
    {
        $response = new ResponseSuccess();
        
        $message = $this->actionManager->emptyWatchlist($watchlistId);
        list($updatedWatchlist, $count) = $this->getUpdatedWatchlist($moduleId, $watchlistId);
        
        $response->setResult(new ResponseData('', ['message' => $message, 'watchlist' => $updatedWatchlist, 'count' => $count]));
        
        return $response;
    }
    
    /**
     * delete specific watchlist
     *
     * @param int $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistDeleteWatchlistAction(int $moduleId, int $watchlistId)
    {
        $response = new ResponseSuccess();
        
        $message = $this->actionManager->deleteWatchlist($watchlistId);
        $user    = FrontendUser::getInstance();
        list($updatedWatchlist, $count) = $this->getUpdatedWatchlist($moduleId, $user->id);
        
        $response->setResult(new ResponseData('', ['message' => $message, 'watchlist' => $updatedWatchlist, 'count' => $count]));
        
        return $response;
    }
    
    public function watchlistDownloadAllAction($watchlistId)
    {
        if (null === ($items = System::getContainer()->get('huh.watchlist.watchlist_item_manager')->getItemsFromWatchlist($watchlistId))) {
            return new ResponseError();
        }
        
        $watchlistName = static::XHR_GROUP;
        
        if (null !== ($watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistModel(null, $watchlistId))) {
            $watchlistName = (WatchlistManager::WATCHLIST_SESSION_BE == $watchlist->name
                              || WatchlistManager::WATCHLIST_SESSION_FE == $watchlist->name) ? $watchlistName : $watchlist->name;
        }
        
        $fileName = 'files/tmp/download_' . $watchlistName . '.zip';
        
        $zipWriter = new ZipWriter($fileName);
        
        
        foreach ($items as $item) {
            if (!$item->download) {
                continue;
            }
            
            if (null === ($watchlistFile = $this->framework->getAdapter(FilesModel::class)->findByUuid($item->uuid))) {
                continue;
            }
            
            $zipWriter->addFile($watchlistFile->path, $watchlistFile->title);
        }
        
        $zipWriter->close();
        
        
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['file' => $fileName]));
        
        // Open the "save as …" dialogue
        return $response;
        
        
    }
    
    
    /**
     * @param $moduleId
     * @param $watchlistId
     *
     * @return array
     */
    public function getUpdatedWatchlist($moduleId, $watchlistId)
    {
        if (null === ($watchlistItems = $this->getCurrentWatchlistItems($moduleId, $watchlistId))) {
            $template        = new FrontendTemplate('watchlist');
            $template->empty = $GLOBALS['TL_LANG']['WATCHLIST_ITEMS']['empty'];
            
            return [$template->parse(), 0];
        }
        
        if (null === ($module = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module', $moduleId))) {
            $template        = new FrontendTemplate('watchlist');
            $template->empty = $GLOBALS['TL_LANG']['WATCHLIST_ITEMS']['empty'];
            
            return [$template->parse(), 0];
        }
        
        return [$this->watchlistTemplate->getWatchlist($module, $watchlistItems), $watchlistItems->count()];
        
        
    }
    
    public function watchlistUpdateAction($id)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData($this->updateWatchlist($id)));
        
        return $response;
    }
    
    
    /**
     * get the template for options of item
     *
     * @param $options
     *
     * @return ResponseSuccess
     */
    protected function getWatchlistItemOptionsModal($moduleId, $type, $options)
    {
        $template = new FrontendTemplate('watchlist_add_option_modal');
        
        $selectTemplate = new FrontendTemplate('watchlist_select_actions');
        
        $selectTemplate->label  = $GLOBALS['TL_LANG']['WATCHLIST']['selectOption'];
        $selectTemplate->select = $options;
        
        $template->options  = $selectTemplate->parse();
        $template->abort    = $GLOBALS['TL_LANG']['WATCHLIST']['abort'];
        $template->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
        $template->addLink  = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $template->moduleId = $moduleId;
        $template->type     = $type;
        $template->action   =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ACTION);
        
        return $this->getModalResponse($template->parse());
    }
    
    
    protected function getModalResponse($content)
    {
        $template          = new FrontendTemplate('watchlist_modal_wrapper');
        $template->content = $content;
        
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['response' => $template->parse()]));
        
        return $response;
        
    }
    
    
    /**
     * check if a entity has options
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
     * check if configured field exists
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
    
    /**
     * show the add action modal
     *
     * @param int $moduleId
     * @param     $itemData
     *
     * @return ResponseSuccess
     */
    //int $id, int $cid, $type, int $pageID, string $title
    public function watchlistShowModalAddAction(int $moduleId, $itemData)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['response' => $this->getWatchlistAddModal($moduleId, $itemData)]));
        
        return $response;
    }
    
    
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
     * @param      $moduleId
     * @param null $watchlistId
     *
     * @return string
     */
    protected function getWatchlistModal($moduleId, $watchlistId = null)
    {
        if (null === ($module = $this->framework->getAdapter(ModuleModel::class)->findByPk($moduleId))) {
            return '';
        }
        
        $template = new FrontendTemplate('watchlist_modal');
        
        $template->watchlistHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['headline'];
        $template->watchlist         = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        
        $watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistModel($moduleId, $watchlistId);
        
        if (null === $watchlist && $module->useMultipleWatchlist && FE_USER_LOGGED_IN) {
            $template->multiple         = true;
            $template->actions          = true;
            $template->watchlistOptions = $this->getWatchlistOptions($module);
        }
        
        if (null === ($watchlistItems = $this->getCurrentWatchlistItems($module, $watchlistId))) {
            $template->cssClass = 'empty';
            
            return $template->parse();
        }
        
        $template->watchlist = $this->watchlistTemplate->getWatchlist($module, $watchlistItems);
        $template->count     = $watchlistItems->count();
        $template->cssClass  = 'not-empty';
        
        return $template->parse();
    }
    
    
    /**
     * get all options for multiple watchlists
     *
     * @param $module
     *
     * @return array
     */
    protected function getWatchlistOptions($module)
    {
        return $this->watchlistManager->getWatchlistModel($module->id);
        
//        if ($module->useGroupWatchlist) {
//            $watchlist = $this->watchlistManager->getWatchlistByGroups($module);
//        } else {
//            $watchlist = $this->watchlistManager->getWatchlistByCurrentUser();
//        }
//
//        if (empty($watchlist)) {
//            return [];
//        }
//
//        return $watchlist;
    }
    
    /**
     * get current watchlist
     *
     * @param $module
     *
     * @return string
     */
    protected function getCurrentWatchlistItems($module, $watchlistId = null)
    {
        if (null === $module && null === $watchlistId) {
            return null;
        }
        
        if (null !== $watchlistId
            && null !== ($items = System::getContainer()->get('huh.watchlist.watchlist_item_manager')->getItemsFromWatchlist($watchlistId))) {
            return $items;
        }
        
        if ($module->useGroupWatchlist) {
            $watchlist = $this->watchlistManager->getWatchlistByModuleConfig($module);
        } else {
            $watchlist = $this->watchlistManager->getWatchlistByUser();
        }
        
        if (null === $watchlist) {
            return null;
        }
        
        if (null === ($watchlistItems = $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlist->id))) {
            return null;
        }
        
        return $watchlistItems;
    }
    
    protected function getWatchlistAddModal(int $moduleId, $itemData)
    {
        if (null === ($module = $this->framework->getAdapter(ModuleModel::class)->findByPk($moduleId))) {
            return null;
        }
        
        // if multiple watchlists are not allowed add the item to the watchlist and return the message
        if (!$module->useMultipleWatchlist) {
            if (null === ($watchlist = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistModel($moduleId))) {
                return new ResponseError();
            }
            
            return $this->addItemToWatchlist($watchlist->id, 'file', $itemData);
        }
        
        $wrapperTemplate = new FrontendTemplate('watchlist_modal_wrapper');
        $template        = new FrontendTemplate('watchlist_add_modal');
        
        if (!empty($options = $this->getWatchlistOptions($module))) {
            $template->watchlistOptions = $options;
        }
        
        $template->newWatchlist = $GLOBALS['TL_LANG']['WATCHLIST']['newWatchlist'];
        
        
        if ($module->addDurability) {
            $template->durability      =
                [$GLOBALS['TL_LANG']['WATCHLIST']['durability']['default'], $GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal']];
            $template->durabilityLabel = $GLOBALS['TL_LANG']['WATCHLIST']['durability']['label'];
        }
        
        $template->newWatchlist    = $GLOBALS['TL_LANG']['WATCHLIST']['newWatchlist'];
        $template->addTitle        = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
        $template->addLink         = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $template->selectWatchlist = $GLOBALS['TL_LANG']['WATCHLIST']['selectWatchlist'];
        $template->active          = true;
        $template->abort           = $GLOBALS['TL_LANG']['WATCHLIST']['abort'];
        
        
        $wrapperTemplate->content = $template->parse();
        
        return $wrapperTemplate->parse();
    }
    
    /**
     * @param $moduleId
     *
     * @return array|WatchlistModel|\Model|null|string
     */
    protected function updateWatchlist($moduleId)
    {
        return $this->getWatchlistModal($moduleId);
    }
    
    protected function addItemToWatchlist($watchlistId, $type, $itemData)
    {
        $response = new ResponseSuccess();
        
        if (is_array($itemData)) {
            $tmp             = $itemData;
            $itemData        = new \stdClass();
            $itemData->title = $tmp['title'];
            $itemData->uuid  = $tmp['uuid'];
        } else {
            $itemData = json_decode($itemData);
        }
        
        if (null === ($responseData =
                System::getContainer()->get('huh.watchlist.action_manager')->addItemToWatchlist($watchlistId, $type, $itemData))) {
            return new ResponseError();
        }
        
        $count = 0;
        if (null !== ($watchlistItems = System::getContainer()->get('huh.watchlist.watchlist_item_manager')->getItemsFromWatchlist($watchlistId))) {
            $count = $watchlistItems->count();
        }
        
        
        $response->setResult(new ResponseData('', ['message' => $responseData, 'count' => $count]));
        
        return $response;
    }
    
    
}