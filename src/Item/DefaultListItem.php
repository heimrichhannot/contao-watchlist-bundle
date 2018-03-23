<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\FrontendTemplate;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistTemplateManager;

class DefaultListItem extends \HeimrichHannot\ListBundle\Item\DefaultItem
{
    /**
     * add the add-to-watchlist button to the template.
     *
     * @return string
     */
    public function getAddToWatchlistButton()
    {
        $module = $this->getModule();
        
        if (null !== ($listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($module['listConfig']))) {
            return System::getContainer()
                ->get('huh.watchlist.template_manager')
                ->getAddToWatchlistButton($this->_raw, $this->_dataContainer, $listConfig->watchlist_config);
        }
    }
}
