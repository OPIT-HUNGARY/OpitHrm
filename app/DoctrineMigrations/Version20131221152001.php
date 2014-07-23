<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131221152001 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_users ADD bank_account_number VARCHAR(50) NOT NULL, ADD bank_name VARCHAR(30) NOT NULL, ADD tax_identification INT NULL");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_8E744D49BFA0107D ON opithrm_users (tax_identification)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP INDEX UNIQ_8E744D49BFA0107D ON opithrm_users");
        $this->addSql("ALTER TABLE opithrm_users DROP bank_account_number, DROP bank_name, DROP tax_identification");
    }
}
