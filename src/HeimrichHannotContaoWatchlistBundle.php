<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle;

use HeimrichHannot\WatchlistBundle\DependencyInjection\HeimrichHannotContaoWatchlistExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoWatchlistBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new HeimrichHannotContaoWatchlistExtension();
    }
}
