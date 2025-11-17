<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Model;

/**
 * @property int $id
 * @property int $pid
 * @property string $tstamp
 * @property string $type
 * @property string $title
 * @property string $file
 * @property string $entityTable
 * @property int $entity
 * @property string $entityUrl
 * @property string $entityFile
 */
class WatchlistItemModel extends Model
{
    protected static $strTable = 'tl_watchlist_item';
}
