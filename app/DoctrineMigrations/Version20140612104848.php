<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140612104848 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        // Abort if duplicates exsist
        $duplicates = $this->connection->fetchAssoc('SELECT COUNT(id) AS duplicates FROM notes_leave_dates GROUP BY leaveDate HAVING duplicates > 1');
        $this->abortIf($duplicates['duplicates'] > 0, 'Unique index cannot be added due to duplicate(s). Please remove those manually before excecuting this migration.');

        $this->addSql("CREATE UNIQUE INDEX UNIQ_842D1E7C4ED5DE29 ON notes_leave_dates (leaveDate)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("DROP INDEX UNIQ_842D1E7C4ED5DE29 ON notes_leave_dates");
    }
}
