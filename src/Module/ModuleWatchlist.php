<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
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

class ModuleWatchlist extends Module
{
    const MODULE_WATCHLIST = 'huhwatchlist';
    protected $strTemplate = 'mod_watchlist';

    /**
     * @var ContaoFramework
     */
    protected $framework;

    public function __construct(ModuleModel $objModule)
    {
        $this->framework = System::getContainer()->get('contao.framework');

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

        if (!System::getContainer()->get('huh.watchlist.watchlist_manager')->checkPermission($this)) {
            return;
        }

        if (Request::getGet('file')) {
            Controller::sendFileToBrowser(Request::getGet('file'));
        }

        return parent::generate();
    }

    protected function compile()
    {
        list($watchlist, $toggler) = System::getContainer()->get('huh.watchlist.template_manager')->getWatchlistToggler($this->id);

        $this->Template->toggler = $toggler;

        if ($this->useGlobalDownloadAllAction) {
            $this->Template->downloadAllAction = System::getContainer()->get('huh.watchlist.template_manager')->getDownloadAllAction($watchlist, $this->id);
        }
    }
}
