<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140513152023 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_employees ADD employeeName VARCHAR(25) NOT NULL");

        // Migrate data from user to employee attributes
        $this->addSql("UPDATE opithrm_employees e, opithrm_users u SET e.employeeName = u.employeeName WHERE u.employee_id = e.id");

        $this->addSql("ALTER TABLE opithrm_users DROP employeeName");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_employees DROP employeeName");
        $this->addSql("ALTER TABLE opithrm_users ADD employeeName VARCHAR(25) NOT NULL");
    }
}
