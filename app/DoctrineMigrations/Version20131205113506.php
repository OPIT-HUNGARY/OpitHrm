<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131205113506 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        if ($schema->getTable('opithrm_tr_destination')->hasIndex('UNIQ_4ACB4B27B2DE2FB7')) {
            $this->write("<info>Unique index found on transportationType_id, altering...</info>");
            $this->addSql("ALTER TABLE opithrm_tr_destination DROP INDEX UNIQ_4ACB4B27B2DE2FB7, ADD INDEX IDX_4ACB4B27B2DE2FB7 (transportationType_id)");
        }
        $this->addSql("ALTER TABLE opithrm_travel_request CHANGE opportunity_name opportunity_name VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $this->write("<info>No schema modifications applied to keep data consistency.</info>");
    }
}
