<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration is an extenstion for data insertion of travel related status workflow
 * The old migration "20140626093909" is modified but does not guarnatee consistent data
 * for environments who already passed that migration.
 */
class Version20140721085509 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $exists = $this->connection->fetchColumn("SELECT COUNT(id) AS `exists` FROM notes_status_workflow WHERE discr = 'travel'");

        // Skip if data already migrated
        $this->skipIf((bool) !$exists, "Travel status workflow migration already done, this migration will be skipped.");

        $this->addSql("UPDATE notes_status_workflow SET discr = 'travelExpense' WHERE discr = 'travel'");
        $this->addSql("INSERT INTO notes_status_workflow (parent_id, status_id, discr) VALUES (NULL, 1, 'travelRequest'), (1, 2, 'travelRequest'), (2, 3, 'travelRequest'), (2, 4, 'travelRequest'), (3, 2, 'travelRequest'), (2, 5, 'travelRequest')");
    }

    public function down(Schema $schema)
    {
        // Prior status workflow migration will take care of reverse migration. Nothing to do here.
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
    }
}
