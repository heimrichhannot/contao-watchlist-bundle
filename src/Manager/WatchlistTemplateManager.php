<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.03.18
 * Time: 12:11
 */

namespace HeimrichHannot\WatchlistBundle\Model;


use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;

class WatchlistTemplateManager
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }
    
    /**
     * @param      $module
     * @param      $items
     * @param bool $grouped
     *
     * @return string
     */
    public function getWatchlist($module, $items, $grouped = true)
    {
        $template = new FrontendTemplate($grouped ? 'watchlist_grouped' : 'watchlist');
        
        $preparedWatchlistItems = $this->prepareWatchlistItems($items, $module, $grouped);
        
        $template->pids  = array_keys($preparedWatchlistItems['parents']);
        $template->items = $preparedWatchlistItems['arrItems'];
        $template->css   = $preparedWatchlistItems['isImage'] = true ? 'watchlist-item-image-list' : '';
        
        return $template->parse();
    }
    
    /**
     * @param         $items
     * @param boolean $grouped
     * @param         $module
     *
     * @return array
     */
    public function prepareWatchlistItems($items, $module, $grouped)
    {
        $parsedItems = [];
        $parents     = [];
        $i           = 0;
        $isImage     = false;
        $totalCount  = $items->countAll();
        
        while ($items->next()) {
            ++$i;
            
            $cssClass = trim(($i == 1 ? 'first ' : '') . ($i == $totalCount ? 'last ' : '') . ($i % 2 == 0 ? 'odd ' : 'even '));
            
            if (!isset($parents[$item->pageID])) {
                
                $parentTemplate          = new FrontendTemplate('watchlist_parents');
                $parentTemplate->items   = $this->getParentList($items->pageID, $module);
                $parents[$items->pageID] = $parentTemplate->parse();
            }
            
            $itemTemplate           = new FrontendTemplate('watchlist_item');
            $itemTemplate->cssClass = $cssClass;
            $result                 = $this->parseItem($items, $module);
            $itemTemplate->item     = $result['item'];
            $isImage                = $result['isImage'];
            $itemTemplate->id       = $this->framework->getAdapter(StringUtil::class)->binToUuid($items->uuid);
            
            
            if ($grouped) {
                $parsedItems[$items->pageID]['page']              = $parents[$items->pageID];
                $parsedItems[$items->pageID]['items'][$items->id] = $itemTemplate->parse();
                
            } else {
                $arrPids[$items->pageID] = $parents[$items->pageID];
                $parsedItems[$items->id] = $itemTemplate->parse();
            }
        }
        
        return ['arrItems' => $parsedItems, 'parents' => $parents, 'isImage' => $isImage];
    }
    
    /**
     * @param int $pageId
     * @param     $module
     *
     * @return array
     */
    
    protected function getParentList(int $pageId, $module)
    {
        $page   = $this->framework->getAdapter(PageModel::class)->findByPk($pageId);
        $type   = null;
        $pageId = $page->id;
        $pages  = [$page->row()];
        $items  = [];
        
        // Get all pages up to the root page
        $pages = $this->framework->getAdapter(PageModel::class)->findParentsById($page->pid);
        
        if ($pages !== null) {
            while ($pageId > 0 && $type != 'root' && $pages->next()) {
                $type    = $pages->type;
                $pageId  = $pages->pid;
                $pages[] = $pages->row();
            }
        }
        
        // Get the first active regular page and display it instead of the root page
        if ($type == 'root') {
            
            $firstPage = $this->framework->getAdapter(PageModel::class)->findFirstPublishedByPid($pages->id);
            
            $items[] = [
                'isRoot'   => true,
                'isActive' => false,
                'href'     => (($firstPage !== null) ? $this->framework->getAdapter(Controller::class)
                    ->generateFrontendUrl($firstPage->row()) : Environment::get('base')),
                'title'    => specialchars($pages->pageTitle ?: $pages->title, true),
                'link'     => $pages->title,
                'data'     => $firstPage->row(),
                'class'    => '',
            ];
            
            array_pop($pages);
        }
        
        // Build the breadcrumb menu
        for ($i = (count($pages) - 1); $i > 0; $i--) {
            if (($pages[$i]['hide'] && !$module->showHidden) || (!$pages[$i]['published'] && !BE_USER_LOGGED_IN)) {
                continue;
            }
            
            // Get href
            switch ($pages[$i]['type']) {
                case 'redirect':
                    $href = $pages[$i]['url'];
                    
                    if (strncasecmp($href, 'mailto:', 7) === 0) {
                        $href = $this->framework->getAdapter(StringUtil::class)->encodeEmail($href);
                    }
                    break;
                
                case 'forward':
                    $objNext = $this->framework->getAdapter(PageModel::class)->findPublishedById($pages[$i]['jumpTo']);
                    
                    if ($objNext !== null) {
                        $href = $this->framework->getAdapter(Controller::class)->generateFrontendUrl($objNext->row());
                        break;
                    }
                // DO NOT ADD A break; STATEMENT
                
                default:
                    $href = $this->framework->getAdapter(Controller::class)->generateFrontendUrl($pages[$i]);
                    break;
            }
            
            $items[] = [
                'isRoot'   => false,
                'isActive' => false,
                'href'     => $href,
                'title'    => specialchars($pages[$i]['pageTitle'] ?: $pages[$i]['title'], true),
                'link'     => $pages[$i]['title'],
                'data'     => $pages[$i],
                'class'    => '',
            ];
        }
        
        // Active page
        $items[] = [
            'isRoot'   => false,
            'isActive' => true,
            'href'     => $this->framework->getAdapter(Controller::class)->generateFrontendUrl($pages[0]),
            'title'    => specialchars($pages[0]['pageTitle'] ?: $pages[0]['title']),
            'link'     => $pages[0]['title'],
            'data'     => $pages[0],
            'class'    => 'last',
        ];
        
        $items[0]['class'] = 'first';
        
        return $items;
    }
    
    
    /**
     * @param integer $downloadLink
     *
     * @return string
     */
    public function getDownloadLinkAction($downloadLink)
    {
        $template = new FrontendTemplate('watchlist_downloadLink_action');
        
        $template->useDownloadLink   = true;
        $template->downloadLinkHref  =
            AjaxAction::generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DOWNLOAD_LINK_ACTION, ['id' => $downloadLink]);
        $template->downloadLinkTitle = $GLOBALS['TL_LANG']['WATCHLIST']['downloadLinkTitle'];
        
        return $template->parse();
    }
    
    /**
     * @return string
     */
    public function getDeleteWatchlistAction()
    {
        $template = new \FrontendTemplate('watchlist_delete_watchlist_action');
        
        $template->delAllHref  = AjaxAction::generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DELETE_ALL_ACTION);
        $template->delAllLink  = $GLOBALS['TL_LANG']['WATCHLIST']['delAllLink'];
        $template->delAllTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delAllTitle'];
        
        return $template->parse();
    }
    
    
    /**
     * @param WatchlistItemModel $item
     * @param ModuleModel        $module
     *
     * @return array
     */
    protected function parseItem(WatchlistItemModel $item, $module)
    {
        /** @var $objPage \Contao\PageModel */
        global $objPage;
        
        $isImage  = false;
        $basePath = $objPage->getFrontendUrl();
        
        
        if (\Input::get('auto_item')) {
            $basePath .= '/' . \Input::get('auto_item');
        }
        
        $objFileModel = \FilesModel::findById($item->uuid);
        
        if ($objFileModel === null) {
            return ['isImage' => $isImage, 'item' => ''];
        }
        
        $objFile = new File($objFileModel->path, true);
        
        
        $objContent = $this->framework->getAdapter(ContentModel::class)->findByPk($item->cid);
        
        $objT = new FrontendTemplate('watchlist_view_download');
        $objT->setData($objFileModel->row());
        
        $linkTitle = specialchars($objFile->name);
        
        // use generate for download & downloads as well
        if ($objContent->type == 'download' && $objContent->linkTitle != '') {
            $linkTitle = $objContent->linkTitle;
        }
        
        $arrMeta = deserialize($objFileModel->meta);
        
        // Language support
        if (($arrLang = $arrMeta[$GLOBALS['TL_LANGUAGE']]) != '') {
            $linkTitle = $arrLang['title'] ? $arrLang['title'] : $linkTitle;
        }
        
        $objT->icon    = TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon;
        $objT->isImage = $objFile->isImage;
        if ($objFile->isImage) {
            $isImage     = true;
            $objT->image = $objFile->path;
            
            // resize image if set
            if ($module->imgSize != '') {
                $image = [];
                
                $size = deserialize($module->imgSize);
                
                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                    $image['size'] = $module->imgSize;
                }
                
                if ($objFileModel->path) {
                    $image['singleSRC'] = $objFileModel->path;
                    $this->framework->getAdapter(Controller::class)->addImageToTemplate($objT, $image);
                }
            }
        }
        
        $objT->link      = ($itemTemplateitle = $item->title) ? $itemTemplateitle : $linkTitle;
        $objT->download  = $item->type == WatchlistItemModel::WATCHLIST_ITEM_TYPE_DOWNLOAD ? true : false;
        $objT->href      = $basePath . '?file=' . $objFile->path;
        $objT->filesize  = $this->framework->getAdapter(System::class)->getReadableSize($objFile->filesize, 1);
        $objT->mime      = $objFile->mime;
        $objT->extension = $objFile->extension;
        $objT->path      = $objFile->dirname;
        $objT->id        = $this->framework->getAdapter(StringUtil::class)->binToUuid($item->uuid);
        
        $objT->actions = $this->getEditActions($item);
        
        return ['item' => $objT->parse(), 'isImage' => $isImage];
    }
    
    /**
     * @param WatchlistItemModel $objItem
     *
     * @return string
     */
    public function getEditActions(WatchlistItemModel $objItem)
    {
        if (null === ($page = $this->framework->getAdapter(PageModel::class)->findByPk($objItem->pageID))) {
            return '';
        }
        
        $template           = new FrontendTemplate('watchlist_edit_actions');
        $template->delHref  = AjaxAction::generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DELETE_ACTION,
            ['id' => $this->framework->getAdapter(StringUtil::class)->binToUuid($objItem->uuid)]);
        $template->delTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delTitle'];
        $template->delLink  = $GLOBALS['TL_LANG']['WATCHLIST']['delLink'];
        $template->id       = $this->framework->getAdapter(StringUtil::class)->binToUuid($objItem->uuid);
        
        return $template->parse();
    }
    
    
    /**
     * generate the add-to-watchlist button
     *
     * @param array  $data
     * @param string $dataContainer
     * @param int    $watchlistConfig
     *
     * @return string
     */
    public function getAddToWatchlistButton(array $data, string $dataContainer, int $watchlistConfig)
    {
        $template        = new FrontendTemplate('watchlist_add_action');
        $template->added = false;
        
        if (null === ($file = deserialize($data['uploadedFiles'])[0])) {
            return '';
        }
        
        if (System::getContainer()->get('huh.watchlist.watchlist_item_manager')->isItemInWatchlist($file)) {
            $template->added = true;
        }
    
        $template->id            = $data['id'];
        $template->moduleId      = $watchlistConfig;
        $template->dataContainer = $dataContainer;
        $template->action        =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ACTION);
        $template->title         = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['addTitle'], $data['title']);
        $template->link          = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];
        
        return $template->parse();
    }
}