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
    //$GLOBALS['TL_HOOKS']['getContentElement']

    /**
     * @param $model
     * @param string $buffer
     * @param $objElement
     * @return string
     */
    public function onGetContentElement(ContentModel $model, string $buffer, ContentElement $objElement)
    {
//        if ($objElement->type === 'downloads')
//        {
//            return $buffer;
//        }
        return $buffer;
    }
}