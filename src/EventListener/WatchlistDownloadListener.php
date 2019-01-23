<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener;

use Contao\DataContainer;
use Contao\System;

class WatchlistDownloadListener
{
    public function sendDownloadLink(DataContainer $dc)
    {
        $moduleId = System::getContainer()->get('huh.request')->getPost('moduleId');
        $watchlistId = System::getContainer()->get('huh.request')->getPost('watchlistId');
    }
}
