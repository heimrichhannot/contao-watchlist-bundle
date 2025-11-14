<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotWatchlistBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

}
