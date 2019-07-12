<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\ListItem;

use Contao\PageModel;
use Contao\System;
use HeimrichHannot\ListBundle\Item\DefaultItem;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\PartialTemplate\AddToWatchlistActionPartialTemplate;
use HeimrichHannot\WatchlistBundle\PartialTemplate\PartialTemplateBuilder;

class WatchlistListItem extends DefaultItem
{
    /**
     * add the add-to-watchlist button to the template.
     *
     * @return string
     */
    public function getAddToWatchlistButton()
    {
        /** @var PageModel $objPage */
        global $objPage;

        $listConfig = $this->getManager()->getListConfig();
        $configuration = WatchlistConfigModel::findByPk($listConfig->watchlistConfig);
        if (!$configuration) {
            return '';
        }
        $watchlistModel = System::getContainer()->get('huh.watchlist.watchlist_manager')->getWatchlistModel($configuration);
        return System::getContainer()->get(PartialTemplateBuilder::class)->generate(
            new AddToWatchlistActionPartialTemplate($configuration, $this->_dataContainer, $this->uuid, $this->title, $watchlistModel, $objPage->id)
        );
    }
}
