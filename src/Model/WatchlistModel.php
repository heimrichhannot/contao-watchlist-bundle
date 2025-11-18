<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\BackendUser;
use Contao\FrontendUser;
use Contao\Model;
use HeimrichHannot\WatchlistBundle\Watchlist\AuthorType;

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

    public function setUser(BackendUser|FrontendUser|string|bool|null $user): void
    {
        $authorType = AuthorType::NONE;
        $author = '';
        if ($user instanceof BackendUser) {
            $authorType = AuthorType::USER;
            $author = $user->id;
        } elseif ($user instanceof FrontendUser) {
            $authorType = AuthorType::MEMBER;
            $author = $user->id;
        } elseif (is_string($user) && !empty($user)) {
            $authorType = AuthorType::SESSION;
            $author = $user;
        }

        $this->authorType = $authorType->value;
        $this->author = $author;
        $this->save();
    }
}
