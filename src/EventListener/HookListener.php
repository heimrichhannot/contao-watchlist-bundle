<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\WatchlistBundle\EventListener;


use Contao\ContentElement;
use Contao\ContentModel;

class HookListener
{
    /**
     * @param $model
     * @param string $buffer
     * @param $objElement
     * @return string
     */
    public function onGetContentElement($model, $buffer, $objElement)
    {
//        if ($objElement->type === 'downloads')
//        {
//            return $buffer;
//        }
        return $buffer;
    }
}