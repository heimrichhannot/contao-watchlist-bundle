<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;

class WatchlistItem implements WatchlistItemInterface
{
    protected $_raw = [];

    protected $_title;

    protected $_file = null;

    protected $_type;

    public function __construct(array $data = [])
    {
        $this->setRaw($data);
    }

    public function getRaw(): array
    {
        return $this->_raw;
    }

    public function setRaw(array $data = []): void
    {
        $this->_raw = $data;
    }

    public function setType(string $type = '')
    {
        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setTitle(string $title = '')
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function setFile()
    {
    }

    public function getEditActions(ModuleModel $module)
    {
        $template = new FrontendTemplate('watchlist_edit_actions');
        $template->id = $this->_raw['id'];
        $template->deleteAction = System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_DELETE_ITEM_ACTION);
        $template->delTitle = $GLOBALS['TL_LANG']['WATCHLIST']['delTitle'];
        $template->delLink = $GLOBALS['TL_LANG']['WATCHLIST']['delLink'];
        $template->moduleId = $module->id;

        if ($this->_raw['download'] && null !== ($file = $this->getFile())) {
            $template->downloadAction = System::getContainer()->get('huh.utils.url')->getCurrentUrl(['skipParams' => true]).'?file='.$file;
            $template->downloadTitle = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['downloadTitle'], $this->getTitle());
        }

        return $template->parse();
    }
}
