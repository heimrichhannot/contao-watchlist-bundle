<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Model;
use Contao\System;

class WatchlistModel extends Model
{
    const WATCHLIST_SELECT = 'watchlist_select';

    protected static $strTable = 'tl_watchlist';

    public function findModelInstanceByPk($pk, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
            static::$strTable,
            $pk,
            $options
        );
    }

    public function findOnePublishedById($id, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy(
            static::$strTable,
            [static::$strTable.'.id=?', static::$strTable.'.published=?'],
            [$id, 1],
            $options
        );
    }

    public function findOnePublishedByPid($pid, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy(
            static::$strTable,
            [static::$strTable.'.pid=?', static::$strTable.'.published=?'],
            [$pid, 1],
            $options
        );
    }

    public function findPublishedByPids(array $ids, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            static::$strTable,
            [static::$strTable.'.pid IN(?)', static::$strTable.'.published=?'],
            [implode(',', $ids), 1],
            $options
        );
    }

    public function findByHashAndName(string $hash, string $name, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            static::$strTable,
            [static::$strTable.'.hash=?', static::$strTable.'.name=?'],
            [$hash, $name],
            $options
        );
    }

    public function findPublishedByUuid($uuid, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            static::$strTable,
            [static::$strTable.'.uuid=UNHEX(?)', static::$strTable.'.published=?'],
            [$uuid, 1],
            $options
        );
    }

    public function findOnePublishedBySessionId($sessionId, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            static::$strTable,
            [static::$strTable.'.sessionID=?', static::$strTable.'.published=?'],
            [$sessionId, 1],
            $options
        );
    }

    public function findByNameAndPid($name, $pid, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy(
            static::$strTable,
            [static::$strTable.'.name=?', static::$strTable.'.pid=?'],
            [$name, $pid],
            $options
        );
    }
}
