<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.03.18
 * Time: 08:45
 */

namespace HeimrichHannot\WatchlistBundle\Manager;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
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
    const XHR_PARAMETER_WATCHLIST_ITEM_UUID             = 'uuid';
    const XHR_PARAMETER_WATCHLIST_ITEM_TITLE          = 'title';
    const XHR_PARAMETER_WATCHLIST_ITEM_DATA_CONTAINER = 'dataContainer';
    const XHR_PARAMETER_WATCHLIST_ITEM_PAGE           = 'pageID';
    const XHR_PARAMETER_WATCHLIST_ITEM_TYPE           = 'type';
    const XHR_PARAMETER_WATCHLIST_NAME                = 'watchlist';
    const XHR_PARAMETER_WATCHLIST_DURABILITY          = 'durability';
    const XHR_PARAMETER_WATCHLIST_ITEM_OPTIONS        = 'options';
    const XHR_WATCHLIST_ADD_ACTION                    = 'watchlistAddAction';
    const XHR_WATCHLIST_DELETE_ACTION                 = 'watchlistDeleteAction';
    const XHR_WATCHLIST_DELETE_ALL_ACTION             = 'watchlistDeleteAllAction';
    const XHR_WATCHLIST_DELETE_ALL_FROM_LIST_ACTION   = 'watchlistDeleteAllItemsFromWatchlistAction';
    const XHR_WATCHLIST_UPDATE_ACTION                 = 'watchlistUpdateAction';
    const XHR_WATCHLIST_SELECT_ACTION                 = 'watchlistSelectAction';
    const XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION       = 'watchlistUpdateModalAddAction';
    const XHR_WATCHLIST_DOWNLOAD_LINK_ACTION          = 'watchlistDownloadLinkAction';
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
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_ALL_FROM_LIST_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DELETE_ALL_ACTION, $this);
        System::getContainer()->get('huh.ajax')->runActiveAction(static::XHR_GROUP, static::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION, $this);
    }
    
    protected function addAjaxActions()
    {
        $GLOBALS['AJAX'][static::XHR_GROUP] = [
            'actions' => [
                static::XHR_WATCHLIST_SHOW_MODAL_ACTION          => [
                    'arguments' => [
                        static::XHR_PARAMETER_MODULE_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_ADD_ACTION                 => [
                    'arguments' => [
                        static::XHR_PARAMETER_MODULE_ID,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_OPTIONS,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_UUID
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_SHOW_MODAL_ADD_ACTION      => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_UPDATE_ACTION              => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_UPDATE_MODAL_ADD_ACTION    => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION       => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_DELETE_ACTION              => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_DELETE_ALL_ACTION          => [
                    'arguments' => [],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_SELECT_ACTION              => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_MULTIPLE_ADD_ACTION        => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
                        static::XHR_PARAMETER_WATCHLIST_NAME,
                        static::XHR_PARAMETER_WATCHLIST_DURABILITY,
                    ],
                    'optional'  => [],
                ],
                static::XHR_WATCHLIST_MULTIPLE_SELECT_ADD_ACTION => [
                    'arguments' => [
                        static::XHR_PARAMETER_WATCHLIST_ITEM_ID,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TYPE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_PAGE,
                        static::XHR_PARAMETER_WATCHLIST_ITEM_TITLE,
                        static::XHR_PARAMETER_WATCHLIST_NAME,
                    ],
                    'optional'  => [],
                ],
            ],
        ];
    }
    
    /**
     * return the success response
     *
     * @param $id
     *
     * @return ResponseSuccess
     */
    public function watchlistUpdateAction($id)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData($this->updateWatchlist($id)));
        
        return $response;
    }
    
    /**
     * @param $moduleId
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistShowModalAction($moduleId)
    {
        if (null === $moduleId) {
            return new ResponseError();
        }
        
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('',['modal' => $this->getWatchlistModal(($moduleId))]));
        
        return $response;
    }
    
    /**
     * clicked on the add to watchlist button
     *
     * @param int    $moduleId
     * @param string $type
     * @param null   $options
     * @param null   $uuid
     *
     * @return ResponseError|ResponseSuccess
     */
    public function watchlistAddAction($moduleId, $type, $options = null, $uuid = null)
    {
        if(null !== $options)
        {
            return $this->getWatchlistItemOptionsModal($options);
        }
    
        if(null === $uuid)
        {
            return new ResponseError();
        }
        
        if (FE_USER_LOGGED_IN) {
            return $this->watchlistShowModalAddAction($uuid, $moduleId);
        }
        
        
        return $this->addItemToWatchlist($uuid, Session::getInstance()->get(WatchlistModel::WATCHLIST_SELECT));
    }
    
    
    /**
     * get the template for options of item
     *
     * @param $options
     *
     * @return ResponseSuccess
     */
    protected function getWatchlistItemOptionsModal($options)
    {
        $template = new FrontendTemplate('watchlist_add_option_modal');
        
        $selectTemplate = new FrontendTemplate('watchlist_select_actions');
        
        $selectTemplate->label  = $GLOBALS['TL_LANG']['WATCHLIST']['selectOption'];
        $selectTemplate->select = $options;
        
        $template->options  = $selectTemplate->parse();
        $template->abort    = $GLOBALS['TL_LANG']['WATCHLIST']['abort'];
        $template->addTitle = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
        $template->addLink = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $template->action   =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ACTION);
        
        return $this->getModalResponse($template->parse());
    }
    
    
    protected function getModalResponse($content)
    {
        $template          = new FrontendTemplate('watchlist_modal_wrapper');
        $template->content = $content;
        
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['modal' => $template->parse()]));
        
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
        
        foreach (deserialize($module->fileFieldEntity, true) as $field) {
            $options[] = $this->checkEntityField($item, $dataContainer, $field);
        }
        
        foreach (deserialize($module->fileFieldChildEntity, true) as $field) {
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
            if (null !== ($items = $this->framework->getAdapter(FilesModel::class)->findMultipleByUuids(deserialize($item->{$field})))) {
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
     * @param int $itemId
     * @param int $moduleId
     *
     * @return ResponseSuccess
     */
    //int $id, int $cid, $type, int $pageID, string $title
    public function watchlistShowModalAddAction(int $itemId, int $moduleId)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['modal' => $this->getWatchlistAddModal($itemId, $moduleId), 'id' => $itemId]));
        
        return $response;
    }
    
    /**
     * delete specific item from watchlist
     *
     * @param int $itemId
     *
     * @return ResponseSuccess
     */
    public function watchlistDeleteItemAction(int $itemId)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData(['id' => $itemId, 'notification' => $this->actionManager->deleteWatchlistItem($itemId)]));
        
        return $response;
    }
    
    
    /**
     * delete all items from specific watchlist
     *
     * @param int $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistDeleteAllItemsFromWatchlistAction(int $watchlistId)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData([
            'id'           => $watchlistId,
            'notification' => $this->actionManager->deleteWatchlistItemFromWatchlist($watchlistId)
        ]));
        
        return $response;
    }
    
    /**
     * delete specific watchlist
     *
     * @param int $watchlistId
     *
     * @return ResponseSuccess
     */
    public function watchlistDeleteWatchlistAction(int $watchlistId)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData(['id' => $watchlistId, 'notification' => $this->actionManager->deleteWatchlist($watchlistId)]));
        
        return $response;
    }
    
    
    public function watchlistGenerateDownloadLinkAction(int $watchlistId, int $moduleId)
    {
        $response = new ResponseSuccess();
        $response->setResult(new ResponseData(false));
        
        if (!isset($watchlistId)) {
            $watchlistId = Session::getInstance()->get(WatchlistModel::WATCHLIST_SELECT);
        }
        
        
        if (null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findOnePublishedById($watchlistId))) {
            return $response;
        }
        
        $response->setResult(new ResponseData([
            'id'           => $watchlistId,
            'notification' => $this->actionManager->generateDownloadLink($watchlistId, $moduleId)
        ]));
        
        return $response;
    }
    
    
    /**
     * @param $moduleId
     *
     * @return string
     */
    protected function getWatchlistModal($moduleId)
    {
        if (null === ($module = $this->framework->getAdapter(ModuleModel::class)->findByPk($moduleId))) {
            return '';
        }
        
        $template = new FrontendTemplate('watchlist_modal');
        $count    = 0;
        
        $template->watchlistHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['headline'];
        $template->watchlist         = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        
        if ($module->useMultipleWatchlist) {
            $template->multiple         = true;
            $template->actions          = true;
            $template->watchlistOptions = $this->getWatchlistOptions($module);
        }
        
        if (null === ($watchlistItems = $this->getCurrentWatchlistItems($module))) {
            $template->cssClass = 'empty';
            
            return $template->parse();
        }
        
        
        $template->watchlist = $this->watchlistTemplate->getWatchlist($module, $watchlistItems);
        $template->count     = $watchlistItems->countAll();
        $template->cssClass  = 'not-empty';
        
        if ($module->useDownloadLink) {
            $template->actions            = true;
            $template->downloadLinkAction = $this->watchlistTemplate->getDownloadLinkAction($module->downloadLink);
        }
        
        $template->deleteWatchlistAction = $this->watchlistTemplate->getDeleteWatchlistAction();
        
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
        if ($module->useGroupWatchlist) {
            $watchlist = $this->watchlistManager->getAllWatchlistsByUserGroups($module->groupWatchlist);
        } else {
            $watchlist = $this->watchlistManager->getAllWatchlistsByCurrentUser();
        }
        
        if (null === ($watchlist)) {
            return [];
        }
        
        $options = [];
        while ($watchlist->next()) {
            $options[$watchlist->id] = $watchlist->name;
        }
        
        asort($options);
        
        return $options;
    }
    
    /**
     * get current watchlist
     *
     * @param $module
     *
     * @return string
     */
    protected function getCurrentWatchlistItems($module)
    {
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
    
    protected function getWatchlistAddModal(int $itemId, int $moduleId)
    {
        if (null === ($module = $this->framework->getAdapter(ModuleModel::class)->findByPk($moduleId))) {
            return null;
        }
        
        // if multiple watchlists are not allowed add the item to the watchlist and return the message
        if (!$module->useMultipleWatchlist) {
            $user = FrontendUser::getInstance();
            
            return $this->addItemToWatchlist($itemId, $user->id);
        }
        
        $wrapperTemplate = new FrontendTemplate('watchlist_modal_wrapper');
        $template        = new FrontendTemplate('watchlist_add_modal');
        
        if (!empty($options = $this->getWatchlistOptions($module))) {
            $template->watchlistOptions = $this->getSelectAction($itemId);
        }
        
        $template->newWatchlist = $GLOBALS['TL_LANG']['WATCHLIST']['newWatchlist'];
        
        
        if ($module->addDurability) {
            $template->durability      =
                [$GLOBALS['TL_LANG']['WATCHLIST']['durability']['default'], $GLOBALS['TL_LANG']['WATCHLIST']['durability']['immortal']];
            $template->durabilityLabel = $GLOBALS['TL_LANG']['WATCHLIST']['durability']['label'];
        }


//        $template->addHref         = System::getContainer()
//            ->get('huh.ajax.action')
//            ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_MULTIPLE_ADD_ACTION,
//                ['id' => $id, 'cid' => $data['id'], 'type' => $data['type'], 'pageID' => $data['pageID'], 'title' => $data['name']]);
//        $template->selectAddHref   = System::getContainer()
//            ->get('huh.ajax.action')
//            ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_MULTIPLE_SELECT_ADD_ACTION,
//                ['id' => $id, 'cid' => $data['id'], 'type' => $data['type'], 'pageID' => $data['pageID'], 'title' => $data['name']]);
        $template->newWatchlist    = $GLOBALS['TL_LANG']['WATCHLIST']['newWatchlist'];
        $template->addTitle        = $GLOBALS['TL_LANG']['WATCHLIST']['addTitle'];
        $template->addLink         = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        $template->selectWatchlist = $GLOBALS['TL_LANG']['WATCHLIST']['selectWatchlist'];
//        $template->watchlistTitle  = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['watchlistModalTitle'], $data['name']);
        $template->active = true;
        $template->abort  = $GLOBALS['TL_LANG']['WATCHLIST']['abort'];
//        $template->id              = $id;
        
        
        $wrapperTemplate->content = $template->parse();
        
        return $wrapperTemplate->parse();
    }
    
    
    /**
     * @param integer $id
     * @param mixed   $groups
     *
     * @return string
     */
    public function getSelectAction($id, $groups = false)
    {
        $select   = $this->watchlistManager->getAllWatchlistsByCurrentUser(true, $groups);
        $selected = Session::getInstance()->get(WatchlistModel::WATCHLIST_SELECT);
        
        if (empty($selected)) {
            $selected = 0;
        }
        
        $template                  = new FrontendTemplate('watchlist_select_actions');
        $template->select          = $select;
        $template->id              = $id;
        $template->selected        = $selected;
        $template->selectWatchlist = $GLOBALS['TL_LANG']['WATCHLIST']['selectWatchlist'];
        
        return $template->parse();
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
    
    
    protected function addItemToWatchlist($id, $watchlistId, $cid = null, $type = null, $pageID = null, $title = null)
    {
        $response = new ResponseSuccess();
        
        if (null === $watchlistId) {
            $response->setResult(new ResponseData(false));
            
            return $response;
        }
        
        
        if (null === ($file = $this->framework->getAdapter(FilesModel::class)->findByUuid($id))) {
            $response->setResult(new ResponseData(false));
            
            return $response;
        }
        
        $notification = $this->actionManager->addWatchlistItem($id, $watchlistId, $cid, $type, $pageID, $title);
        $response->setResult(new ResponseData(['id' => $id, 'notification' => $notification]));
        
        return $response;
    }
}