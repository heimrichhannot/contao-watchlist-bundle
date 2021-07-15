<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

class WatchlistItemContainer
{
    public function listChildren($arrRow)
    {
        return '<div class="tl_content_left">'.($arrRow['title'] ?: $arrRow['id']).' <span style="color:#b3b3b3; padding-left:3px">['.
            \Date::parse(\Contao\Config::get('datimFormat'), trim($arrRow['dateAdded'])).']</span></div>';
    }
}
