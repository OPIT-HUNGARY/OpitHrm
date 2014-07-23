<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140709153903 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $group = $this->connection->fetchAssoc('SELECT COUNT(id) as group_count FROM opithrm_groups WHERE role="ROLE_SYSTEM_ADMIN"');
        if (0 == $group['group_count']) {
            $this->addSql("INSERT INTO opithrm_groups (name, role) VALUES ('System admin', 'ROLE_SYSTEM_ADMIN')");
        }

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
    }
}
