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
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\Ajax\AjaxAction;
use HeimrichHannot\Request\Request;
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
        $template = new FrontendTemplate('watchlist');
        
        $preparedWatchlistItems = $this->prepareWatchlistItems($items, $module, $grouped);
        
        if (!empty($preparedWatchlistItems['parents'])) {
            $template->pids = array_keys($preparedWatchlistItems['parents']);
        }
        
        if (!empty($preparedWatchlistItems['items'])) {
            $template->items = $preparedWatchlistItems['items'];
        }
        
        // get download link action
        if ($module->useDownloadLink) {
            $template->actions            = true;
            $template->downloadLinkAction = $this->getDownloadLinkAction($module->downloadLink);
        }
        
        // get delete watchlist action
        if ($module->useMultipleWatchlist) {
            $template->actions               = true;
            $template->deleteWatchlistAction = $this->getDeleteWatchlistAction($items[0]->pid, $module->id);
        }
        // get empty watchlist action
        else {
            $template->actions              = true;
            $template->emptyWatchlistAction = $this->getEmptyWatchlistAction($items[0]->pid, $module->id);
        }
        
        // get download all action
        if (count($preparedWatchlistItems) > 1) {
            $template->actions           = true;
            $template->downloadAllAction = $this->getDownloadAllAction($items[0]->pid, $module->id);
        }
        
        $template->grouped = $grouped;
        
        return $template->parse();
    }
    
    /**
     * @param $watchlistId
     * @param $moduleId
     *
     * @return string
     *
     */
    public function getDeleteWatchlistAction($watchlistId, $moduleId)
    {
        $template                       = new FrontendTemplate('watchlist_delete_watchlist_action');
        $template->watchlistId          = $watchlistId;
        $template->moduleId             = $moduleId;
        $template->action               = System::getContainer()
            ->get('huh.ajax.action')
            ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DELETE_WATCHLIST_ACTION);
        $template->deleteWatchlistLink  = $GLOBALS['TL_LANG']['WATCHLIST']['delWatchlistLink'];
        $template->deleteWatchlistTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delWatchlistTitle'];
        
        return $template->parse();
    }
    
    /**
     * @param $watchlistId
     * @param $moduleId
     *
     * @return string
     */
    public function getEmptyWatchlistAction($watchlistId, $moduleId)
    {
        $template                      = new FrontendTemplate('watchlist_empty_watchlist_action');
        $template->watchlistId         = $watchlistId;
        $template->moduleId            = $moduleId;
        $template->action              = System::getContainer()
            ->get('huh.ajax.action')
            ->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_EMPTY_WATCHLIST_ACTION);
        $template->emptyWatchlistLink  = $GLOBALS['TL_LANG']['WATCHLIST']['emptyWatchlistLink'];
        $template->emptyWatchlistTitle = $GLOBALS['TL_LANG']['WATCHLIST']['emptyWatchlistTitle'];
        
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
        $totalCount = $items->count();
        
        $parsedItems = [];
        $parents     = [];
        
        foreach ($items as $key => $item) {
            
            $cssClass = trim(($key == 0 ? 'first ' : '') . ($key == $totalCount ? 'last ' : '') . (($key + 1) % 2 == 0 ? 'odd ' : 'even '));
            
            $parsedItem = $this->parseItem($item, $module, $cssClass);
            
            if ($grouped) {
                $parsedItems[$item->pageID]['page']             = $this->framework->getAdapter(PageModel::class)->findByPk($item->pageID)->title;
                $parsedItems[$item->pageID]['items'][$item->id] = $parsedItem;
                
            } else {
                $arrPids[$item->pageID] = $parents[$item->pageID];
                $parsedItems[$item->id] = $parsedItem;
            }
        }
        
        return ['items' => $parsedItems, 'parents' => $parents];
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
     * @param $watchlistId
     * @param $moduleId
     *
     * @return string
     */
    public function getDownloadAllAction($watchlistId, $moduleId)
    {
        $downloadAllTemplate                   = new FrontendTemplate('watchlist_download_all_action');
        $downloadAllTemplate->action           =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DOWNLOAD_ALL_ACTION);
        $downloadAllTemplate->downloadAllLink  = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllLink'];
        $downloadAllTemplate->downloadAllTitle = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllTitle'];
        $downloadAllTemplate->watchlistId      = $watchlistId;
        $downloadAllTemplate->moduleId         = $moduleId;
        
        return $downloadAllTemplate->parse();
    }
    
    /**
     * @param WatchlistItemModel $item
     * @param                    $module
     * @param                    $cssClass
     *
     * @return string
     */
    protected function parseItem(WatchlistItemModel $item, $module, $cssClass)
    {
        $template           = new FrontendTemplate('watchlist_item');
        $template->cssClass = $cssClass;
        $template->id       = $item->id;
        $template->title    = $item->title;
        $template->type     = $item->type;
        $template->download = $item->download ? true : false;
        
        if ($item->uuid && null !== ($file = $this->framework->getAdapter(FilesModel::class)->findByUuid($item->uuid))) {
            $template->image = $file;
            
            $image = [
                'singleSRC' => $file->path
            ];
            
            if ($module->imgSize) {
                $image['size'] = $module->imgSize;
            }
            
            $this->framework->getAdapter(Controller::class)->addImageToTemplate($template, $image);
        }
        
        $template->actions = $this->getEditActions($item, $file, $module);
        
        return $template->parse();
    }
    
    /**
     * @param WatchlistItemModel $item
     * @param FilesModel         $file
     *
     * @return string
     */
    public function getEditActions(WatchlistItemModel $item, FilesModel $file, $module)
    {
        $template               = new FrontendTemplate('watchlist_edit_actions');
        $template->id           = $item->id;
        $template->deleteAction =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DELETE_ITEM_ACTION);
        $template->delTitle     = $GLOBALS['TL_LANG']['WATCHLIST']['delTitle'];
        $template->delLink      = $GLOBALS['TL_LANG']['WATCHLIST']['delLink'];
        $template->moduleId     = $module->id;
        
        if ($item->download && null !== $file) {
            $template->downloadAction = Environment::get('base') . "?file=" . $file->path;
            $template->downloadTitle  = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['downloadTitle'], $item->title);
        }
        
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