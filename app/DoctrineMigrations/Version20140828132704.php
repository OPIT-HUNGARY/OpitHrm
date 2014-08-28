<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140828132704 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE opithrm_common_types (id INT AUTO_INCREMENT NOT NULL, created_user_id INT DEFAULT NULL, updated_user_id INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, system TINYINT(1) DEFAULT '0' NOT NULL, name LONGTEXT NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_78FC93D0E104C1D3 (created_user_id), INDEX IDX_78FC93D0BB649746 (updated_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_common_types ADD CONSTRAINT FK_78FC93D0E104C1D3 FOREIGN KEY (created_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_common_types ADD CONSTRAINT FK_78FC93D0BB649746 FOREIGN KEY (updated_user_id) REFERENCES opithrm_users (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE opithrm_common_types");
    }
}
