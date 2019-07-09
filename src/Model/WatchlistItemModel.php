<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Model;

use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;

/**
 * Class WatchlistItemModel
 * @package HeimrichHannot\WatchlistBundle\Model
 *
 * @property int $id
 * @property int $pid
 * @property string $uuid
 * @property int $pageID
 * @property int $tstamp
 * @property string $title
 * @property string $download
 * @property string $parentTable
 * @property string $parentTableId
 * @property string $type
 */
class WatchlistItemModel extends Model
{
    const WATCHLIST_ITEM_TYPE_FILE = 'file';
    const WATCHLIST_ITEM_TYPE_ENTITY = 'entity';

    protected static $strTable = 'tl_watchlist_item';

    public function __construct($objResult = null)
    {
        parent::__construct($objResult);

        $this->container = System::getContainer();
    }


    public function findByPidAndUuid($pid, $uuid, array $options = [])
    {
        if (Validator::isStringUuid($uuid)) {
            $uuid = StringUtil::uuidToBin($uuid);
        }

        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(static::$strTable, [static::$strTable.'.pid=?', static::$strTable.'.uuid=?'], [$pid, $uuid], $options);
    }

    public function findByUuid($uuid, array $options = [])
    {
        if (Validator::isStringUuid($uuid)) {
            $uuid = StringUtil::uuidToBin($uuid);
        }

        return System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy(static::$strTable, [static::$strTable.'.uuid=UNHEX(?)'], [$uuid], $options);
    }

    public function findByPid($pid, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(static::$strTable, [static::$strTable.'.pid=?'], [$pid], $options);
    }

    public function findByPidAndPtableAndPtableId($pid, $ptable, $ptableId, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(static::$strTable, [static::$strTable.'.pid=?', static::$strTable.'.ptable=?', static::$strTable.'.ptableId=?'], [$pid, $ptable, $ptableId], $options);
    }

    public function findInstanceByPk($pk, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy(static::$strTable, [static::$strTable.'.id=?'], [$pk], $options);
    }

    public function countByPid($pid)
    {
        return System::getContainer()->get('contao.framework')->getAdapter(self::class)->countBy('pid', $pid);
    }

    /**
     * Returns the path to the file
     *
     * @return string|null
     */
    public function getFilePath()
    {
        if (static::WATCHLIST_ITEM_TYPE_FILE === $this->type)
        {
            return $this->container->get('huh.utils.file')->getPathFromUuid($this->uuid);
        }
        return null;
    }
}
