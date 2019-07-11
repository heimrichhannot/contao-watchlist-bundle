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


use HeimrichHannot\WatchlistBundle\Controller\WatchlistActionController;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistActionManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocaleListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
//        $event->getRequest()->getPathInfo()
    }
}