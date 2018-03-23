<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 19.03.18
 * Time: 10:51
 */

namespace HeimrichHannot\WatchlistBundle\Module;


use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Module;
use Contao\PageModel;
use HeimrichHannot\Request\Request;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Symfony\Component\Translation\Translator;
use Contao\Controller;

class ModuleWatchlistDownloadList extends Module
{
    protected $strTemplate = 'mod_watchlist_download_list';
    
    const MODULE_WATCHLIST_DOWNLOAD_LIST = 'huhwatchlist_downloadlist';
    
    /**
     * @var ContaoFramework
     */
    protected $framework;
    
    
    public function __construct(ContaoFrameworkInterface $framework, $module)
    {
        $this->framework = $framework;
        
        parent::__construct($module);
    }
    
    
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate           = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['watchlist'][0]) . ' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
        
            return $objTemplate->parse();
        }
        $GLOBALS['TL_JAVASCRIPT']['watchlist'] = 'system/modules/watchlist/assets/js/jquery.watchlist.js|static';
    
        if (Request::getGet('file')) {
            Controller::sendFileToBrowser(Request::getGet('file'));
        }
    
        
        if (!Request::getGet('watchlist')) {
            return '';
        }
    
    
        return parent::generate();
    }
    
    
    
    protected function compile()
    {
        /** @var PageModel $objPage */
        global $objPage;
    
        $id        = Request::getGet('watchlist');
        
        if(null === ($watchlist = $this->framework->getAdapter(WatchlistModel::class)->findPublishedByUuid(Request::getGet('watchlist'))))
        {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }
        
        
        if (!$this->checkWatchlistValidity($watchlist)) {
            /** @var \PageError404 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
        }
        
        $array = $this->getWatchlistItemsForDownloadList($watchlist);
        if (empty($array['items'])) {
            $this->Template->empty = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];
        }
        $watchlistController                  = new WatchlistController();
        $this->Template->downloadAllButton    = $array['downloadAllButton'];
        $this->Template->items                = $array['items'];
        $this->Template->downloadAllHref      = $watchlistController->downloadAll($watchlist);
        $this->Template->downloadAllLink      = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAll'];
        $this->Template->downloadAllTitle     = $GLOBALS['TL_LANG']['WATCHLIST']['downloadAllSecondTitle'];
        $this->Template->downloadListHeadline = $GLOBALS['TL_LANG']['WATCHLIST']['downloadListHeadline'];
    }
}