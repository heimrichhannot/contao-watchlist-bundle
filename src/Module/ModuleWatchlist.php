<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Module;

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\Module;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\Request\Request;
use Patchwork\Utf8;
use Psr\Container\ContainerInterface;

class ModuleWatchlist extends Module
{
    const MODULE_WATCHLIST = 'huhwatchlist';

    protected $strTemplate = 'mod_watchlist';

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ModuleModel $objModule)
    {

        $this->container = System::getContainer();

        parent::__construct($objModule);
    }

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['watchlist'][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        if (!$this->container->get('huh.watchlist.watchlist_manager')->checkPermission($this)) {
            return;
        }

        if (Request::getGet('file')) {
            Controller::sendFileToBrowser(Request::getGet('file'));
        }

        return parent::generate();
    }

    protected function compile()
    {
        list($watchlist, $toggler) = $this->container->get('huh.watchlist.template_manager')->getWatchlistToggler($this->id);

        $this->Template->toggler = $toggler;

        if ($this->useGlobalDownloadAllAction) {
            $this->Template->downloadAllAction = $this->container->get('huh.watchlist.template_manager')->getDownloadAllAction($watchlist, $this->id);
        }
    }
}
