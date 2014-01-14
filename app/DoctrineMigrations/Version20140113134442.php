<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140113134442 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_te_paid_expense ADD paid_in_advance TINYINT(1) DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_travel_expense CHANGE advances_recieved advances_recieved DOUBLE PRECISION NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_te_paid_expense DROP paid_in_advance");
        $this->addSql("ALTER TABLE notes_travel_expense CHANGE advances_recieved advances_recieved TINYINT(1) NOT NULL");
    }
}
