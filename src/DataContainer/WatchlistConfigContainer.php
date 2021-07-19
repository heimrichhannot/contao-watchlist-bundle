<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

class WatchlistConfigContainer
{
    protected DcaUtil             $dcaUtil;

    public function __construct(DcaUtil $dcaUtil)
    {
        $this->dcaUtil = $dcaUtil;
    }

    /**
     * @Callback(table="tl_watchlist_config", target="config.onsubmit")
     */
    public function setDateAdded(DataContainer $dc)
    {
        $this->dcaUtil->setDateAdded($dc);
    }

    /**
     * @Callback(table="tl_watchlist_config", target="config.oncopy")
     */
    public function setDateAddedOnCopy($insertId, DataContainer $dc)
    {
        $this->dcaUtil->setDateAddedOnCopy($insertId, $dc);
    }
}
