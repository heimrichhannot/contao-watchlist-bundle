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

class Version303Migration extends AbstractMigration
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_watchlist_item'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_watchlist_item');

        return
            isset($columns['uuid']) &&
            !isset($columns['file']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeQuery('
            ALTER TABLE
                tl_watchlist_item
            ADD
                file binary(16) NULL
        ');

        $stmt = $this->connection->prepare('
            UPDATE
                tl_watchlist_item
            SET
                file = uuid
        ');

        $stmt->executeQuery();

        return $this->createResult(
            true,
            'Moved tl_watchlist_item.uuid to tl_watchlist_item.file for '.$stmt->rowCount().' records.'
        );
    }
}
