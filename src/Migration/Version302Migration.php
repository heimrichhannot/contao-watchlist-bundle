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

class Version302Migration extends AbstractMigration
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
            isset($columns['parenttableid']) &&
            !isset($columns['entity']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeQuery("
            ALTER TABLE
                tl_watchlist_item
            ADD
                entity int(10) unsigned NOT NULL default '0'
        ");

        $stmt = $this->connection->prepare('
            UPDATE
                tl_watchlist_item
            SET
                entity = parentTableId
        ');

        $stmt->executeQuery();

        return $this->createResult(
            true,
            'Moved tl_watchlist_item.parentTableId to tl_watchlist_item.entity for '.$stmt->rowCount().' records.'
        );
    }
}
