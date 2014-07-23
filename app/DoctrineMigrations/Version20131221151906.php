<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131221151906 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_travel_expense ADD travelRequest_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE opithrm_travel_expense ADD CONSTRAINT FK_45CCB7FD4663DE14 FOREIGN KEY (travelRequest_id) REFERENCES opithrm_travel_request (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_45CCB7FD4663DE14 ON opithrm_travel_expense (travelRequest_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_travel_expense DROP FOREIGN KEY FK_45CCB7FD4663DE14");
        $this->addSql("DROP INDEX UNIQ_45CCB7FD4663DE14 ON opithrm_travel_expense");
        $this->addSql("ALTER TABLE opithrm_travel_expense DROP travelRequest_id");
    }
}
