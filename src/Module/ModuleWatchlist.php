<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Module;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\Request\Request;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistItemManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Symfony\Component\Translation\Translator;

class ModuleWatchlist extends Module
{
    const MODULE_WATCHLIST = 'huhwatchlist';
    protected $strTemplate = 'mod_watchlist';

    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var WatchlistManager
     */
    protected $watchlistManager;

    /**
     * @var WatchlistItemManager
     */
    protected $watchlistItemManager;

    public function __construct(ModuleModel $objModule)
    {
        $this->framework = System::getContainer()->get('contao.framework');
        $this->translator = System::getContainer()->get('translator');
        $this->watchlistManager = System::getContainer()->get('huh.watchlist.watchlist_manager');
        $this->watchlistItemManager = System::getContainer()->get('huh.watchlist.watchlist_item_manager');

        parent::__construct($objModule);
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['watchlist'][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        if (!$this->watchlistManager->checkPermission($this)) {
            return;
        }

        if (Request::getGet('file')) {
            Controller::sendFileToBrowser(Request::getGet('file'));
        }

        return parent::generate();
    }

    protected function compile()
    {
        $count = 0;
        $this->Template->watchlist = $GLOBALS['TL_LANG']['WATCHLIST']['empty'];

        /* @var $watchlist WatchlistModel */
        if (null === ($watchlist = $this->watchlistManager->getWatchlistModel($this->id))) {
            $count = 0;
        }

        if (null !== $watchlist && null !== ($watchlistItems = $this->watchlistManager->getItemsFromWatchlist($watchlist->id))) {
            $count = $watchlistItems->count();
        }

        $this->Template->count = $count;
        $this->Template->toggleLink = $GLOBALS['TL_LANG']['WATCHLIST']['toggleLink'];
        $this->Template->moduleId = $this->id;
        $this->Template->currentWatchlist = $watchlist->id;
        $this->Template->action = System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_SHOW_MODAL_ACTION);
    }
}
