<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\WatchlistBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class Version300Migration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_watchlist'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_watchlist');

        return
            isset($columns['name']) &&
            !isset($columns['title']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeQuery("
            ALTER TABLE
                tl_watchlist
            ADD
                title varchar(255) NOT NULL DEFAULT ''
        ");

        $stmt = $this->connection->prepare('
            UPDATE
                tl_watchlist
            SET
                title = name
        ');

        $result = $stmt->executeQuery();

        return $this->createResult(
            true,
            'Moved tl_watchlist.name to tl_watchlist.title for '.$result->rowCount().' records.'
        );
    }
}
