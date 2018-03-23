<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 19.03.18
 * Time: 10:52
 */

namespace HeimrichHannot\WatchlistBundle\Model;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use Contao\System;
use Contao\Validator;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class WatchlistItemModel extends Model
{
    const WATCHLIST_ITEM_TYPE_DOWNLOAD    = 'download';
    const WATCHLIST_ITEM_TYPE_NO_DOWNLOAD = 'no_download';
    
    protected static $strTable = 'tl_watchlist_item';
    
    public function findByPidAndUuid($pid, $uuid, array $options = [])
    {
        if(Validator::isStringUuid($uuid))
        {
            $uuid = StringUtil::uuidToBin($uuid);
        }
        
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            static::$strTable,
            [static::$strTable . '.pid=?', static::$strTable . '.uuid=?'],
            [$pid, $uuid],
            $options
        );
    }
    
    public function findByUuid($uuid, array $options = [])
    {
        if(Validator::isStringUuid($uuid))
        {
            $uuid = StringUtil::uuidToBin($uuid);
        }
        
        return System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy(
            static::$strTable,
            [static::$strTable . '.uuid=UNHEX(?)'],
            [$uuid],
            $options
        );
    }
    
    public function findByPid($pid, array $options = [])
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            static::$strTable,
            [static::$strTable . '.pid=?'],
            [$pid],
            $options
        );
    }
}