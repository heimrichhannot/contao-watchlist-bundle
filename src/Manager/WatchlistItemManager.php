<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 19.03.18
 * Time: 16:18
 */

namespace HeimrichHannot\WatchlistBundle\Manager;


use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\System;
use Contao\StringUtil;
use HeimrichHannot\Request\Request;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;

class WatchlistItemManager
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    
    protected $module;
    
    
    public function __construct(ContaoFrameworkInterface $framework, $module)
    {
        $this->framework = $framework;
        $this->module    = $module;
    }
    
    public function getItemsFromWatchlist(int $watchlist)
    {
        return $this->framework->getAdapter(WatchlistItemModel::class)->findByPid($watchlist);
    }
    
    public function getWatchlistIdFromItem(int $itemId)
    {
        if(null === ($watchlistItem = $this->framework->getAdapter(WatchlistItemModel::class)->findInstanceByPk($itemId)))
        {
            return null;
        }
        
        return $watchlistItem->pid;
    }
    
    
    public function prepareItem($item)
    {
        /** @var $objPage PageModel */
        global $objPage;
        
        $basePath = $objPage->getFrontendUrl();
        
        if (Request::getGet('watchlist')) {
            $basePath .= '?watchlist=' . Request::getGet('watchlist');
        }
        
        
        $template           = new FrontendTemplate('watchlist_download_list_item');
        $template->download = true;
        
        $fileAdapter = $this->framework->getAdapter(FilesModel::class);
        
        if (null === ($file = $fileAdapter->findByUuid($item->uuid))) {
            return;
        }
        
        if (in_array($file->extension, Config::get('validImageTypes'))) {
            $this->addImageToTemplate($template, $file->path);
        }
        
        if ($item->type !== WatchlistItemModel::WATCHLIST_ITEM_TYPE_DOWNLOAD) {
            $template->download = false;
        }
        
        $template->copyright     = $this->getCopyright($file);
        $template->title         = $item->title;
        $template->id            = $item->id;
        $template->filesize      = System::getReadableSize($file->filesize, 1);
        $template->downloadLink  = $basePath . '&file=' . $file->path;
        $template->downloadTitle = $GLOBALS['TL_LANG']['WATCHLIST']['download'];
        $template->noDownload    = $GLOBALS['TL_LANG']['WATCHLIST']['noDownload'];
        
        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['parseWatchlistItems']) && is_array($GLOBALS['TL_HOOKS']['parseWatchlistItems'])) {
            foreach ($GLOBALS['TL_HOOKS']['parseWatchlistItems'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($template, $item, $this);
            }
        }
        
        return $template->parse();
    }
    
    /**
     * get copyright of an file
     *
     * @param $file
     *
     * @return string
     */
    protected function getCopyRight($file)
    {
        $copyrights = StringUtil::deserialize($file, true);
        
        if (empty($copyrights)) {
            return '';
        }
        
        return implode(',', $copyrights);
    }
    
    /**
     * add the image to the template
     *
     * @param FrontendTemplate $template
     * @param string           $path
     */
    protected function addImageToTemplate(FrontendTemplate $template, string $path)
    {
        if (!isset($path)) {
            return;
        }
        
        $template->image = $path;
        
        // resize image if set
        if ($this->module->imgSize != '') {
            $image = [];
            
            $size = StringUtil::deserialize($this->module->imgSize,true);
            
            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                $image['size'] = $this->module->imgSize;
            }
            
            $image['singleSRC'] = $path;
            Controller::addImageToTemplate($template, $image);
        }
    }
    
    /**
     * * check if item has already been added to a watchlist
     *
     * @param int         $watchlistId
     * @param string|null $itemUuid
     * @param int|null    $ptable
     * @param int|null    $ptableId
     *
     * @return bool
     */
    public function isItemInWatchlist(int $watchlistId, string $itemUuid = null, int $ptable = null, int $ptableId = null)
    {
        if (null === $watchlistId) {
            return false;
        }
        
        if (null === $itemUuid && null === $ptable && null === $ptableId) {
            return false;
        }
        
        
        if (null !== $itemUuid) {
            return $this->checkWatchlistForFile($watchlistId, $itemUuid);
        }
        
        if (null !== $ptable && null !== $ptableId) {
            return $this->checkWatchlistForEntity($watchlistId, $ptable, $ptableId);
        }
        
        return false;
    }
    
    /**
     * check item existence in watchlist on uuid
     *
     * @param $watchlistId
     * @param $itemUuid
     *
     * @return bool
     */
    protected function checkWatchlistForFile($watchlistId, $itemUuid)
    {
        if (null === $this->framework->getAdapter(WatchlistItemModel::class)->findByPidAndUuid($watchlistId, $itemUuid)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * check item existence in watchlist on ptable and ptableId
     *
     * @param $watchlistId
     * @param $ptable
     * @param $ptableId
     *
     * @return bool
     */
    protected function checkWatchlistForEntity($watchlistId, $ptable, $ptableId)
    {
        if (null === $this->framework->getAdapter(WatchlistItemModel::class)->findByPidAndPtableAndPtableId($watchlistId, $ptable, $ptableId)) {
            return false;
        }
        
        return true;
    }
    
}