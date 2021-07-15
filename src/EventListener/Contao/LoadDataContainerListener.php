<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    protected DcaUtil $dcaUtil;

    public function __construct(DcaUtil $dcaUtil)
    {
        $this->dcaUtil = $dcaUtil;
    }

    public function __invoke($table)
    {
        if ('tl_watchlist' !== $table) {
            return;
        }

        $this->dcaUtil->addAuthorFieldAndCallback('tl_watchlist');
    }
}
