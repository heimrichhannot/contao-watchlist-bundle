<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $dateAdded
 * @property string $title
 * @property string $watchlistContentTemplate
 * @property string $insertTagAddItemTemplate
 * @property string $addShare
 * @property string $shareJumpTo
 */
class WatchlistConfigModel extends Model
{
    protected static $strTable = 'tl_watchlist_config';
}
