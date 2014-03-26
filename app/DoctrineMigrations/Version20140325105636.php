<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140325105636 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_te_paid_expense CHANGE amount amount NUMERIC(10, 2) NOT NULL");
        $this->addSql("ALTER TABLE notes_tr_destination CHANGE cost cost NUMERIC(10, 2) NOT NULL");
        $this->addSql("ALTER TABLE notes_te_per_diem CHANGE amount amount NUMERIC(10, 2) NOT NULL");
        $this->addSql("ALTER TABLE notes_tr_accomodation CHANGE cost cost NUMERIC(10, 2) NOT NULL");
        $this->addSql("ALTER TABLE notes_te_advances_received CHANGE advances_received advances_received NUMERIC(10, 2) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_te_advances_received CHANGE advances_received advances_received DOUBLE PRECISION NOT NULL");
        $this->addSql("ALTER TABLE notes_te_paid_expense CHANGE amount amount DOUBLE PRECISION NOT NULL");
        $this->addSql("ALTER TABLE notes_te_per_diem CHANGE amount amount DOUBLE PRECISION NOT NULL");
        $this->addSql("ALTER TABLE notes_tr_accomodation CHANGE cost cost INT NOT NULL");
        $this->addSql("ALTER TABLE notes_tr_destination CHANGE cost cost INT NOT NULL");
    }
}
