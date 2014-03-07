<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140307151831 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_travel_expense ADD CONSTRAINT FK_45CCB7FD4663DE14 FOREIGN KEY (travelRequest_id) REFERENCES notes_travel_request (id)");
        $this->addSql("ALTER TABLE notes_travel_request CHANGE opportunity_name customer_name VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_travel_expense DROP FOREIGN KEY FK_45CCB7FD4663DE14");
        $this->addSql("ALTER TABLE notes_travel_request CHANGE customer_name opportunity_name VARCHAR(255) DEFAULT NULL");
    }
}
