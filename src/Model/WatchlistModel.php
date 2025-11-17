<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Model;

/**
 * @property int $id
 * @property int $tstamp
 * @property string $title
 * @property string $uuid
 * @property int $config
 * @property string $authorType
 * @property string $author
 *
 * @method static WatchlistModel|null findByPk($varValue, array $arrOptions = [])
 */
class WatchlistModel extends Model
{
    protected static $strTable = 'tl_watchlist';

    public static function findByUuid(string $uuid, array $options = []): ?self
    {
        $t = static::$strTable;
        return static::findOneBy(["$t.uuid=?"], [$uuid], $options);
    }
}
