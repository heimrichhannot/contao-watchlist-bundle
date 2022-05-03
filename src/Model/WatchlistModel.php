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
 * @property string $title
 * @property string $watchlistContentTemplate
 * @property string $insertTagAddItemTemplate
 */
class WatchlistModel extends Model
{
    protected static $strTable = 'tl_watchlist';
}
