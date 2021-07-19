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

class Version301Migration extends AbstractMigration
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
            isset($columns['parenttable']) &&
            !isset($columns['entitytable']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeQuery("
            ALTER TABLE
                tl_watchlist_item
            ADD
                entityTable varchar(128) NOT NULL DEFAULT ''
        ");

        $stmt = $this->connection->prepare('
            UPDATE
                tl_watchlist_item
            SET
                entityTable = parentTable
        ');

        $stmt->executeQuery();

        return $this->createResult(
            true,
            'Moved tl_watchlist_item.parentTable to tl_watchlist_item.entityTable for '.$stmt->rowCount().' records.'
        );
    }
}
