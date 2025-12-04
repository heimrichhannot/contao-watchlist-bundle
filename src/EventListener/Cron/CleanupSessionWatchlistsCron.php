<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\EventListener\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Doctrine\DBAL\Connection;
use HeimrichHannot\WatchlistBundle\Watchlist\AuthorType;

#[AsCronJob('daily')]
class CleanupSessionWatchlistsCron
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(): void
    {
        $thirtyDaysAgo = strtotime('-30 days');

        $this->connection->executeStatement(
            'DELETE FROM tl_watchlist_item WHERE pid IN (SELECT id FROM tl_watchlist WHERE authorType IN (?, ?) AND tstamp < ?)',
            [AuthorType::SESSION->value, AuthorType::NONE->value, $thirtyDaysAgo]
        );

        $this->connection->executeStatement(
            'DELETE FROM tl_watchlist WHERE authorType IN (?, ?) AND tstamp < ?',
            [AuthorType::SESSION->value, AuthorType::NONE->value, $thirtyDaysAgo]
        );
    }
}
