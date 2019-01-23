<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\ListItem;

use Contao\System;
use HeimrichHannot\ListBundle\Item\DefaultItem;

class WatchlistListItem extends DefaultItem
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
