<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Util;

use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class WatchlistUtil
{
    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }
}
